<?php

namespace App\Http\Controllers;

use App\Traits\TextHandler;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Traits\Document;
use ConvertApi\ConvertApi;
use Illuminate\Support\Facades\Log;

class StatusPortalController extends Controller
{
    use Document;
    use TextHandler;
    public $styles = array ( '*' => 'strong', '_' => 'i', '~' => 'strike');

    public function updateStatus(Request $request)
    {
        $params = $request->all();
        //TODO: comentar esta linea de codigo. Quitar comentario si se desea capturar el request desde SOFTEXPERT
        Log::info('Captura de parametros StatusController',['params' => $params]);

        if($params['record_id'] == null || $params['record_id'] == 'null' || $params['record_id'] ==''){
            $message = "No se ha informad ningun numero de solicitud a actualizar";
            return response()->json(['status' =>['success' => false, 'message' =>$message]]);
        }
        if(($request->method == 'file' || $request->method == 'all') && ($request->documents == '' || $request->documents == 'null') )
        {
            $message = "No es posible ejecutar el método seleccionado '$request->method'. No se han informado documentos para ser enviados al portal";
            return response()->json(['success' => false, 'message' =>$message]);
        }
        if($request->method =='status')
        {
            return  ['status' =>$this->UpdateStatusPortal($params)];
        }
        else if($request->method == 'all')
        {
            $status =  $this->UpdateStatusPortal($params);
            if($status['success'] === true)
            {
                $files = $this->PrepareFiles($params);
                $finalStatus = ['status' => $status, 'files' => $files];
                return response()->json($finalStatus);
            }
            return $status;
        }
        else if($request->method == 'file' && ($request->documents != '' && $request->documents !="null"))
        {
            $files = $this->PrepareFiles($params);
            return ['files' =>$files];
        }
        else if($request->method == 'payment-status')
        {
            $response = ['status' =>$this->UpdatePayment($params)];
            return response()->json($response);
        }
        return response()->json(['status' =>'failure', 'message' =>'Something was wrong with your request.', 'parameters' => $request->all()]);
    }

    public function UpdatePayment($params)
    {
        $body = [
            'authorization_code' => $params['authorization_code'],
            'payment_date' => date('Y-m-d'),
            'record_id' => $params['record_id'],
            'progress' =>null,
            'action_required'=>null,
            'description' => null,
            'activity_id' => null,
            'solution' => null,
            'history'  => null,
            'status'   =>$params['status']
        ];
        $url = env('BACKEND_API_URL').'getstatus';
        $client = new Client(['headers' => ['X-Authorization' => env('BACKEND_API_KEY')],"verify" => false]);
        $rq = $client->post($url, [
            'headers' => [
                'Accept'     => 'application/json'
            ]
            ,'json' => $body
        ]);
        return json_decode($rq->getBody()->getContents(), true);
    }

    public function UpdateStatusPortal($params)
    {
        $history = null;
        if($params['history']  != "null")
        {
            $history = $this->BuildMessageForPortal($params['history']);
            $history = json_encode($history);
        }
        $description = utf8_encode($this->makeBoldText($params['description']));

        if(array_key_exists('monto', $params))
        {
            $mount = str_replace('RD$','',$params['monto']);
            $mount = str_replace('.','',$mount);
            $mount = str_replace(',','.',$mount);
            if($mount == '0'){
                $mount = null;
            }
        }
        else
        {
            $mount = null;
        }

        $params['progress'] = ($params['progress'] == '0' ? 'null' : $params['progress']);
        $body = [
            'record_id' => $params['record_id'],
            'status' => $params['status'],
            'progress' => ($params['progress'] == "null" ? null:  intval($params['progress'])),
            'action_required'=>($params['action_required'] !== "null" ? intval($params['action_required']):  null),
            'description' => ($description == "null"? null:  $description),
            'activity_id' => ($params['activity_id'] == "null" ? null: utf8_encode($params['activity_id'])),
            'solution' => ($params['solution'] == "null" ? null: $this->makeBoldText($params['solution'])),
            'history'  => $history,
            'valor_pago_required' => $mount
        ];

        $url = env('BACKEND_API_URL').'getstatus';
        $client = new Client(['headers' => ['X-Authorization' => env('BACKEND_API_KEY')],"verify" => false]);
        $rq = $client->post($url, [
            'headers' => [
                'Accept'     => 'application/json'
            ]
            ,'json' => $body
        ]);
        return json_decode($rq->getBody()->getContents(), true);
    }

    private function ExplodeDocuments($documents)
    {
        $documentsWithCategory = explode(';',$documents);
        $finalDocuments = [];
        foreach($documentsWithCategory as $doc)
        {
            $separated = explode(',',$doc);
            $actual = array('document_id' =>$separated[0],'category' => $separated[1]);
            array_push($finalDocuments,$actual);
        }
        return $finalDocuments;
    }

    private function PrepareFiles($params)
    {
        $documentsIds = $this->ExplodeDocuments($params['documents']);
        $downloadedDocuments = [];
        $index = 0;
        for ($i=0; $i <count($documentsIds) ; $i++)
        {
            $container = $this->DownloadElectronicFile($documentsIds[$i]['document_id']);
            $filtered = $this->filterByQr($container);
            foreach ($filtered as $document)
            {
                $downloadedDocuments[$index] = $document;
                $index++;
            }
        }
        $finalDocuments[$params['record_id']] = $downloadedDocuments;

        return $this->UploadFileToFileServer(['documents' => $finalDocuments]);
    }

    private function UploadFileToFileServer($files)
    {
        $url = env('FILE_SERVER_URL').'getdocuments';
        $client = new Client(['headers' => ['X-Authorization' => env('BACKEND_API_KEY')],"verify" => false]);
        $rq = $client->post($url, [
            'headers' => [
                'Accept'     => 'application/json'
            ]
            ,'json' => $files
        ]);
        $decode = utf8_decode($rq->getBody()->getContents());

        return json_decode($decode,true);
    }
}
