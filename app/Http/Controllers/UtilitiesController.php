<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use NumerosEnLetras;
use App\Traits\DataQuery;
use App\Traits\GridSoftExpert;
use App\Traits\Workflow;



class UtilitiesController extends Controller
{
    use DataQuery;
    use GridSoftExpert;
    use Workflow;

    public function CounterSimvIncrement(Request $request)
    {
        $query = "UPDATE dynsivclasgendatos SET numero1 = C.Cantidad FROM (SELECT numero1 + 1 'Cantidad' from dynsivclasgendatos where atipoclas = 'Cursos' and bidclasif = '".$request->curso."' ) C where atipoclas = 'Cursos' and bidclasif = '".$request->curso."'";
        $result = DB::connection('sqlsrv')->select($query);

    }

    public function ExplodeStringForSql(Request $request)
    {
        $text = str_replace(' ','',$request->text);
        $elements = explode($request->separator,$text);
        $result = "";
        foreach ($elements as $element)
        {
            if($result == "")
            {
                $result .= "'".$element."'";
            }
            $result .= ",'".$element."'";
        }
        return response()->json(['status' => 'success', 'text' => $result ]);
    }

    public function ConvertDateToLiteralString(Request $request)
    {
        if($request->date == null)
        {
            return response()->json(['message' => 'No se ha informado una fecha para convertir.']);
        }
        $date = Carbon::parse($request->date);

        $month = $date->format("F"); // Inglés.
        $day = NumerosEnLetras::convertir($date->day);
        $year = NumerosEnLetras::convertir($date->year);
        $translate = "months.".$month;

        $month = __($translate);
        return response()->json(['day' => $day, 'month' => $month, 'year' => $year]);
    }

    public function ConvertStringToDate(Request $request)
    {
        $date = substr($request->date,0,10);
        $newTime =  Carbon::createFromFormat('Y-m-d', $date);
        $newTime = Carbon::now()->toIso8601String();
        return response()->json(['date' => $newTime]);
    }
    public function ConvertTimeToLocal(Request $request)
    {
        $newTime = date('g:i a', strtotime($request->time));
        return response()->json(['time' =>$newTime ]);
    }

    private function ExplodeFieldsToArray($fields)
    {
        $fields = explode(';',$fields);
        $newFields = [];
        foreach($fields as $field)
        {
            $current = explode('=',$field);
            array_push($newFields,$current);
        }
        return $newFields;
    }

    public function GetDataTablaSoftexpert(Request $request)
    {
        $codprovincia = $request->codprovincia;
        $coddepart = $request->coddepart;
        $query = "";
        switch ($request->type) {
            case 'zonas-caasd':
                $query = env('QUERY_ZONAS_CAASD');
                $query = str_replace('$codimnmueble',$request->cod_imnmueble,$query);
                break;
            case 'provincias-edesur':
                $query = env('QUERY_PROVINCIAS_PROYECTOS_EDESUR');
                break;
            case 'departamento-edesur':
                $query = env('QUERY_DEPARTAMENTOS_PROYECTOS_EDESUR');
                $query = str_replace('$codprovincia',$request->codprov, $query);
                break;
            case 'municipios-edesur':
                $query = env('QUERY_MUNICIPIOS_PROYECTOS_EDESUR');
                $query = str_replace('$codprov',$request->codprov,$query);
                $query = str_replace('$coddepto',$request->coddepto,$query);
                break;
            case 'barrios-edesur':
                $query = env('QUERY_BARRIO_PROYECTOS_EDESUR');
                $query = str_replace('codprov',$request->codprov,$query);
                $query = str_replace('coddepto',$request->coddepto,$query);
                $query = str_replace('codmuni',$request->codmuni,$query);
                break;
            case 'provincias-edeeste':

                break;
            case 'municipios-edenorte':

                break;
            case 'sectores-edenorte':

                break;

            default:
                # code...
                break;
        }
        $data = $this->select($query);
        if($data)
        {
            return response()->json(['success' => true, 'data' => $data[0]],201);
        }
        return response()->json(['success'=> false, 'message' =>'No se han encontrado registros, revise sun consulta e intente nuevamente']);


    }

    public function GetTarifarioMitur()
    {

    }


    public function CopyDataGridToGrid(Request $request)
    {
        $fromTable = "DYN".$request->origin_child_table;
        $toTable = $request->destiny_main_table;
        $toRelationship = $request->relationship_destiny;
        $fields = $this->ExplodeFieldsToArray($request->fields);

        $fieldsFrom = array_column($fields, 0);
        $fieldsTo = array_column($fields, 1);

        $keyField = $request->keyField;
        $keyFieldValue = $request->keyFieldValue;
        $workflowid = $request->workflowid;
        $query = "SELECT * FROM $fromTable WHERE $keyField = '$keyFieldValue'";

        if($request->filert_by_fecharegistro)
        {
            $query = "SELECT * FROM $fromTable WHERE $keyField = '$keyFieldValue' and a7fecharegistg = (SELECT MAX(a7fecharegistg) FROM $fromTable WHERE $keyField = '$keyFieldValue')";
        }
        //dd($query);
        $result = $this->select($query);

        $result_filtered = $this->filterDataFromQueryResult($result,$fieldsFrom,$fieldsTo);

        $items_decoded = $this->decodeItemsForGrid($result_filtered);

        $data = $this->arrayGrid($items_decoded);

        $gridparams = $this->buildParamToSendGrid($toRelationship,$toTable,$workflowid,$data);

        return $this->newChildEntityRecord($gridparams);

    }


    public function ValidateLab(Request $request)
    {
        /**
         * 1.Capture grid data by relationship field
         * 2.Validate each registry if match with any Lab in clasificador
         * 3.if match copy de actual code lab to the finally colunm
         */

        $query = "SELECT OID FROM dyniibi01solserv WHERE a1numsolicitud='$request->numero_solicitud'";
        $result = DB::connection('sqlsrv')->select($query);

        if($result)
        {
            $oid = $result[0]->OID;
        }
        else
        {
            return response()->json(['success' =>false, 'message'=>'No se ha encontrado una solicitud con este id']);
        }


        $column = env('IIBI_GRID_RELATIONSHIP_COLUMN');
       /*  dd($column); */
        $query ="SELECT * FROM dyniib01asolser WHERE ".$column."='$oid'";
        $cotizaciones = DB::connection('sqlsrv')->select($query);

        $query = "SELECT BIDCLASIF,CTITULOCLAS FROM dyniibiclasgendat WHERE ATIPOCLAS='Laboratorios' and BIDCLASIF is not null";
        $laboratorios = DB::connection('sqlsrv')->select($query);

        $matches = $this->getLabMatches($cotizaciones,$laboratorios);
        $matches = $this->getLabMatches($cotizaciones,$laboratorios);

        if($request->validEmpty == true)
        {
            if(count($matches) > 0)
            {
                return response()->json(['valid' => true]);
            }
            else
            {
                return response()->json(['valid' => false]);
            }
        }
        return $this->updateCotizacionMatched($request->numero_solicitud,$matches);
    }

    private function getLabMatches($cotizaciones,$laboratorios)
    {
        $matches = [];
        foreach($cotizaciones as $cotizacion)
        {
            foreach($laboratorios as $laboratorio)
            {
                if($cotizacion->CODPRODUCTO == $laboratorio->BIDCLASIF)
                {
                    array_push($matches,$cotizacion);
                    continue;
                }
            }
        }
        return $matches;
    }

    private function updateCotizacionMatched($workflowid,$cotizaciones)
    {
        $results = [];
        foreach($cotizaciones as $cotizacion)
        {
            $EntityAttributeList = [
                'EntityAttribute' => [
                    'EntityAttributeID' => 'CODLABORATORIO',
                    'EntityAttributeValue' => $cotizacion->CODPRODUCTO
                ]
            ];

            $result = $this->editChildEntityRecord($workflowid,'iibi01solserv','kdetaanalisis',$cotizacion->OID,$EntityAttributeList);

            array_push($results,$result);
        }

        $encode = json_encode($results);
        $success = true;
        foreach($results as $result)
        {
            if($result['Status'] !='SUCCESS')
            {
                $success = false;
            }
        }
        return ['success' =>$success, 'message' =>$encode];;
    }
}
