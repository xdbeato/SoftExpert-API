<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateMultipleSoftexpertDocuments;
use Illuminate\Http\Request;
use App\Traits\DataQuery;
use App\Traits\Document;
use App\Traits\DataTest;
use App\Traits\Workflow;
use Illuminate\Support\Facades\Log;

class DocumentsController extends Controller
{
    use DataQuery;
    use Document;
    use DataTest;
    use Workflow;

    public $dc;

    public function __construct()
    {
        set_time_limit(8000000);
        $this->dc = new \nusoap_client(env('URL_SOFTEXPERT_WSDL'), 'wsdl');//Esta url es la de la webservice de Workflow, se captura desde un archivo de configuracion (.env)
        $this->dc->setCredentials(env('SE_USER'), env('SE_PASSWORD'));
        $this->dc->soap_defencoding = 'UTF-8';
        $this->dc->decode_utf8 = false;
    }

    public function newDocumentWithModelAndAsociate(Request $request)
    {
        $params = $request->all();
        Log::info('Captura de parametros DocumentsController',['params' => $params]);

        if( !$this->validateIfGridHasContent($request->Attributes) )
        {
            $response  = ['newDocument' => null, 'association' => null, 'error' =>'No se han informado registros del grid para la ejecución de la aplicación', 'status' =>'failure'];
            return response()->json($response);
        }
        $attributes = (array_key_exists('Grid',$request->Attributes ) ? $request->Attributes['Grid'] : []);



        $main_table_attributes = (array_key_exists('MainTable',$request->Attributes) ? $request->Attributes['MainTable'] : null);

        if(!$attributes && $main_table_attributes){
            $main_table_attributes = $this->mainTableToGrid($main_table_attributes);
            $rows = $this->buildRows($main_table_attributes);
        }else{
            $rows = $this->buildRows($attributes);
        }

        if($main_table_attributes && $attributes)
        {
            $rows =  $this->addAttributes($rows,$main_table_attributes);
        }


        if($request->handle_attibutes_portal)
        {
            $portal_attributes = [
                [
                    'AttributeID' =>'idservicio',
                    'AttributeValue' =>$request->workflow_id
                ],
                [
                    'AttributeID' =>'EstadoFinalDoc',
                    'AttributeValue' =>'Disponible Ciudadano'
                ],
            ];
            $rows = $this->addAttributes($rows,$portal_attributes);
        }
        $results = [];
        $ids_createdocs = [];
        $utfRows = [];
        foreach ($rows as $row){
            array_push($utfRows, utf8_encode($row));
        }
        //dd($utfRows);
        $automaticExecution = (count($rows) > 1 ? true : false);

        $job = new GenerateMultipleSoftexpertDocuments($utfRows,$request->document_category,$request->document_tite,$request->association['activity_id'],$request->workflow_id, $automaticExecution);

        dispatch($job);

        return response()->json(['status' =>'success', 'message' => 'correctly scheduled task']);

    }

    private function mainTableToGrid($mainTableAttributes)
    {
        $newAttributes = [];
        foreach ($mainTableAttributes as $attribute)
        {
            $new = [
                "AttributeValueList" =>[
                    [
                        "AttributeValue" => $attribute['AttributeValue']
                    ],

                ],"AttributeID" => $attribute['AttributeID']

            ];
            array_push($newAttributes, $new);
        }

        return $newAttributes;
    }

    private function validateIfGridHasContent($attributes)
    {
        if( !array_key_exists('Grid', $attributes) && !array_key_exists('MainTable', $attributes)  ){
            return false;
        }
        else if( (array_key_exists('Grid', $attributes) && count($attributes['Grid']) > 0  )    ||  (array_key_exists('MainTable', $attributes) && $attributes['MainTable'] > 0)){
            return true;
        }
       return false;
    }
    private function addAttributes($rows,$newAttributes)
    {
        $newRows = [];
        $val = '';
        foreach ($newAttributes as $key => $attribute) {
            if($val === '')
            {
                $val .= $attribute['AttributeID']."=".$attribute['AttributeValue'];
            }
            else
            {
                $val .= ";".$attribute['AttributeID']."=".$attribute['AttributeValue'];
            }
        }

        foreach ($rows as $key => $row) {
            $new = strval($row.";".$val);
            array_push($newRows,$new);
        }

        return $newRows;
    }

    private function buildRows($attributes)
    {
        if(count($attributes) == 0){
            return [];
        }
        $rows = [];
        foreach ($attributes as $key => $attribute) {
            $values = $attribute['AttributeValueList'];
            $row = [$attribute['AttributeID'] =>[]];
            foreach ($values as $key2 => $value) {
                array_push( $row[$attribute['AttributeID'] ],$value['AttributeValue']);
            }
            array_push($rows,$row);
        }

        $actualRows = [];
        foreach ($rows as $key => $row) {
            $actualRows = $this->buildRow($row,$actualRows);
        }
        return $actualRows;

    }

    private function buildRow($colunm,$actualRows)
    {
        $rows = $colunm[key ( $colunm )];

        foreach ($rows as $key => $value) {
            if(array_key_exists($key,$actualRows))
            {
                $actualRows[$key] .= ";".key ( $colunm ).'='.$value;
            }
            else {
                $actualRows[$key] = key ( $colunm ).'='.$value;
            }
        }
        return $actualRows;
    }


    private function replaceQueryAttibutes($search,$replace,$target)
    {
        return str_replace($search,$replace, $target);
    }

    public function searchDocumentData($solicitud)
    {

        $query = env('QUERY_SEARCH_DOCUMENTS');
        $query = str_replace('$solicitud',$solicitud, $query);
        $documents = $this->select($query);

        if($documents)
        {
            return response()->json(['status' => 'success', 'data' => $documents,'message'=> 'Consulta realizada de manera satisfactoria'], 200);
        }

        return response()->json(['status' => 'failure', 'message' => 'No se han encontrado documentos'], 404);
    }


    public function searchDocumentDataByDocumentId($documentid)
    {
        $query = env('QUERY_SEARCH_DOCUMENTS_ATTRIBUTES');
        $query = str_replace('$iddocument',$documentid,$query);
        $document_data = $this->select($query);
        if($document_data)
        {
            return response()->json(['status' => 'success', 'data' => $document_data,'message'=> 'Consulta realizada de manera satisfactoria'], 200);
        }

        return response()->json(['status' => 'failure', 'message' => 'No se han encontrado documentos'], 404);
    }

    public function ChangeDocumentDisponibility(Request $request)
    {

        if($request->documents == "" || $request->idattribute == "" || $request->vlattribute == "")
        {
            return response()->json(['success' => false, 'message' => 'Algunos parámetros no fueron provistos, favor revise e intente nuevamente.']);
        }


        $documents = $this->ExplodeDocuments($request->documents);
        if(!$documents)
        {
            return response()->json(['success' =>false, 'message' =>'No se han informado documentos valdios para modificación']);
        }
        $fails = 0;
        $logs = '';
        foreach($documents as $document)
        {
            $response = $this->setAttributeValue($document['document_id'],$request->idattribute,$request->vlattribute);
            if($response['success'] === false)
            {
                $fails++;
            }
            $logs .= $response['message'].' ';

        }
        return response()->json(($fails > 0 ? ['success' =>false ,'message' =>$logs] : ['success' =>true,'message' =>$logs]));


    }

    private function ExplodeDocuments($documents)
    {
        $documentsWithCategory = explode(';',$documents);

        $final_documents = [];
        foreach($documentsWithCategory as $doc)
        {
            $separated = explode(',',$doc);
            if(count($separated) < 2)
            {
                return [];
            }

            $actual = array('document_id' =>$separated[0],'category' => $separated[1]);
            array_push($final_documents,$actual);
        }
        return $final_documents;
    }



}
