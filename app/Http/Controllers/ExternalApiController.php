<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\DataQuery;
use App\Traits\Document;
use App\Traits\Workflow;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;


class ExternalApiController extends Controller
{
    use DataQuery;
    use Document;
    use Workflow;

    public function cancelacionContratoCaasd(Request $request)
    {
        $query = env('QUERY_SOLICITUD_CAASD');
        $codigo_proceso = ($request->id_proceso ? $request->id_proceso : 'NaN');
        $codigo_sistema = ($request->codigo_sistema ? $request->codigo_sistema : 'NaN');
        $query = str_replace('$codigo_sistema',$codigo_sistema,$query);
        $query = str_replace('$id_proceso',$codigo_proceso,$query);

        $cancel_status = ($request->fecha_corte ? 1 : 2);
        $fields = [
            [
                'key' => 'fecha01',
                'value' => $request->fecha_corte
            ],
            [
                'key' => 'radiobutt08',
                'value' => $cancel_status
            ],
            [
                'key' => 'texto25505',
                'value' => $request->message
            ]
        ];

        $data = $this->select($query);
        if(!$data)
        {
            return response()->json(['success' => false, 'message' =>'No se ha encontrado una solicitud abierta relacionada al número de contrato provisto']);
        }
        $solicitud = $data[0];
        $wfresponse = $this->editEntityRecord($data[0]->workflowid,env('CAASD_TBL_SE'), $fields, false);

        $executeActivity = $this->executeActivity($solicitud, 'CAASD01006');

        if($wfresponse['Status'] == 'SUCCESS')
        {
            return response()->json(['success' => true, 'message' => 'Registro modificado con éxito'],201);
        }

        /**
         * 1. A partir del codigo del sistema obtener el numero de la solicitud
         * 2. Actualizar el estado de la cancelacion en softexpert. Puede ser por Webservice o por base de datos
         * 3. Avanar la solicitud hacia la siguiente actividad
         */
    }

    public function initCancelacionContratoCaasdCompanies(Request $request)
    {
        $documents = $this->GetDocumentBySolicitud($request->solicitud);
        if(!$documents)
        {
            return response()->json(['success' => false, 'message' => 'No se han encontrado documentos relacionados con esta solicitud '],404);
        }

        $prepared_documents = [];
        foreach ($documents as $document) {
            $actual = $this->DownloadElectronicFile($document->iddocument);
            $actual['keyword'] = $document->name;
            array_push($prepared_documents,$actual);
        }
        if($request->company == 'AAA')
        {


            foreach ($prepared_documents as $key => $document) {
                unset($prepared_documents[$key]['keyword']);
                $name = explode('.',$prepared_documents[$key]['name'])[0];
                $prepared_documents[$key]['nombre'] = $name;
                $encode = 'data:'.$document['type'].';base64,'.$document['encode'];
                $prepared_documents[$key]['contenido'] = $encode;
                unset($prepared_documents[$key]['name']);
                unset($prepared_documents[$key]['encode']);
            }
            $params = [
                'inmueble' => $request->codigo_sistema,
                'descripcion' => $request->motivo_cancelacion,
                'documentos' => $prepared_documents

            ];

            $token =  env('TOKEN_CAASD_COMPANY_AAA');
        }
        else
        {
            $date = date('Ymd');
            $token = md5(env('TOKEN_CAASD_COMPANY_ACEA').$date);
            $params = [
                'id_proceso' => $request->id_proceso,
                'descripcion' => $request->motivo_cancelacion,
                'copia_cedula' => $this->getDocumentByKey($prepared_documents,'Copia de Cédula'),
                'copia_carta' => $this->getDocumentByKey($prepared_documents,'Carta de Solicitud de Cancelación'),
                'poder_inmueble' => $this->getDocumentByKey($prepared_documents,'Copia de Título de Propiedad'),

                'token' => $token
            ];

        }
        /* dd($token); */
        $apiurl = env('URL_CAASD_COMPANY_'.$request->company);

        //return response()->json($params);



        if($request->company == 'AAA')
        {
            $client = new Client(["verify" => false, 'headers' => ['Authorization' => 'Token '.$token,'Content-Type' => 'application/json' ]]);
            $response = $client->request('POST', $apiurl, [
                'body' => json_encode($params)
            ]);

        }
        else if($request->company == 'ACEA')
        {
            $client = new Client(["verify" => false]);
            $response = $client->request('POST', $apiurl, [
                'form_params' => $params
            ]);
        }


        return $response->getBody();
    }


    public function getDocumentByKey($documents, $key)
    {
        foreach($documents as $document)
        {
            if($document['keyword'] == $key)
            {
                return $document['encode'];
            }
        }
    }
    public function getDocuments($solicitud)
    {

    }


    public function getProjetsInvi(Request $request)
    {
        $token = Redis::get('invi_tkn');
        if(!$token)
        {
            $token = $this->loginApiInvi();
        }
        $apiurl = env('INVI_PROJECTS_URL').$request->citizen;
        $client = new Client([ 'headers' => ['Content-Type' => 'application/json','Authorization' => 'Bearer '.$token ]]);
        $response = $client->request('GET', $apiurl);

        if($response->getStatusCode() == 200)
        {
            $result = $response->getBody()->getContents();
            $result =  json_decode($result, true);
            return response()->json(['status' =>'sucess','data' =>$result['data']]);

        }
        return response()->json(['status' =>'failure','message' => 'Error al hacer la consulta']);


    }

    public function loginApiInvi()
    {
        $apiurl = env('INVI_PROJECTS_URL_LOGIN');
        $mail = env('INVI_USER_API');
        $password = env('INVI_PASS_API');
        $params = ['email' =>$mail, 'password' =>$password];

        $client = new Client([ 'headers' => ['Content-Type' => 'application/json' ]]);
        $response = $client->request('POST', $apiurl, [
            'body' => json_encode($params)
        ]);

        $result = $response->getBody()->getContents();
        $result = json_decode($result, true);

        $token = $result['access_token'];

        $expire = 3300;
        Redis::set('invi_tkn',$token,'EX',$expire);
        return $token;
    }


}
