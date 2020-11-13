<?php

namespace App\Traits;
use Illuminate\Support\Str;
use Exception;
use App\DocumentResponse;
trait Document {


    public function newDocument($idcategory,$fgmodel = 0,$attributes,$title)
    {
      $dc = new \nusoap_client(env('URL_SOFTEXPERT_WSDL'), 'wsdl');//Esta url es la de la webservice de Workflow, se captura desde un archivo de configuracion (.env)
      $dc->setCredentials(env('SE_USER'), env('SE_PASSWORD'));
      $dc->soap_defencoding = 'UTF-8';
      $dc->decode_utf8 = false;
      //dd($wf);
      $params = [
        'idcategory'=>$idcategory,
        'iddocument'=>'',
        'title'=>$title,
        'dsresume'=> '',
        'dtdocument' => '',
        'attributes' => utf8_decode($attributes),
        'iduser' => '',
        'participants' => [],
        'fgmodel' => $fgmodel
      ];
      //dd($params);
      $request = $dc->call('newDocument', $params);
      //dd($request);
      return  $this->getNewDocumentCallStatus($request,$dc);

    }
    public function setAttributeValue($iddocument,$idattribute,$vlattribute)
    {
      $params = [
        'iddocument' => $iddocument,
        'idrevision' =>'',
        'idattribute' => $idattribute,
        'vlattribute' => $vlattribute
      ];

      $request = $this->dc->call('setAttributeValue',$params);
      $success = null;
      if(array_key_exists('return',$request))
      {
        $success = true;
        $message = utf8_encode($request['return']);
      }
      else if(array_key_exists('faultstring',$request))
      {
        $success = false;
        $message = utf8_encode($request['faultstring']);
      }

      return ['success' =>$success, 'message' =>$message];
    }

    private function getNewDocumentCallStatus($request,$dc)
    {
        if($request === false)
        {
          return ['status' => 'failure', 'documentid' => null,'message' =>$dc->getError()];
        }
        $result = explode(':',$request['return']);
        $message = utf8_decode($request['return']);

        $success = ($result[0] === '1' ? true : false);

        $documentid = ($result[0] == '0' ? null : $result[1]);
        //$message = ($result[0] === '1' ? utf8_decode($result[2]) : utf8_decode($result[1]));

        return  new DocumentResponse($success,str_replace(' ','',$documentid),$message);
    }

    public function DownloadElectronicFile($iddocument)
    {
        $wf = new \nusoap_client(env('URL_SOFTEXPERT_WSDL'), 'wsdl');//Esta url es la de la webservice de Workflow, se captura desde un archivo de configuracion (.env)
        $wf->setCredentials(env('SE_USER'), env('SE_PASSWORD'));
        $wf->soap_defencoding = 'UTF-8';
        $wf->decode_utf8 = false;

        $documentdata = $this->getDocumentData($iddocument);

        $params = ['iddocument'=>$iddocument,'idrevision'=>'00','iduser'=>getenv('SE_USER'),'fgconverttopdf'=> '1'];

        $request = $wf->call('downloadEletronicFile', $params);
        $type = '';
        $documents_array = [];
        //dd($request);
        if(array_key_exists('return',$request))
        {
            if(isset($request['return']['item']['ERROR']))
            {
                die(json_encode(array('status' => ['success' => false, 'message' =>utf8_decode($request['return']['item']['ERROR'])])));
            }
            //es una categoria con archivos simples
            if(isset($request['return']['item']['NMFILE']))
            {
              $document = $request['return']['item'];
              $type = $this->GetDocumentType($document['BINFILE']);

              $name = utf8_decode($documentdata['return']['NMTITLE'])  . ' - '. $this->generateRandomString(10);
              $ext = explode('/', $type)[1];
              $name = $name.'.'.$ext;
              $encode = $document['BINFILE'];
              $pdfname = utf8_decode(explode('.',$document['NMFILE'])[0]);
              array_push($documents_array,array('name' => $name,'pdfname' =>$pdfname, 'encode' => $encode, 'type' => $type));
            }
            else //categoria con multiples archivos
            {
                $documents = $request['return']['item'];
                foreach ($documents as $document) {
                    $type = $this->GetDocumentType($document['BINFILE']);
                    $name = utf8_decode($documentdata['return']['NMTITLE'])  .' - '. $this->generateRandomString(10);
                    $ext = explode('/', $type)[1];
                    $name = $name.'.'.$ext;
                    $encode = $document['BINFILE'];
                    $pdfname = utf8_decode(explode('.',$document['NMFILE'])[0]);
                    array_push($documents_array,array('name' => $name,'pdfname' =>$pdfname, 'encode' => $encode, 'type' => $type));
                }
            }
        }
        return $documents_array;
    }

    function generateRandomString($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++)
        {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }

    public function getDocumentData($iddocument)
    {
        $wf = new \nusoap_client(env('URL_SOFTEXPERT_WSDL'), 'wsdl');//Esta url es la de la webservice de Workflow, se captura desde un archivo de configuracion (.env)
        $wf->setCredentials(env('SE_USER'), env('SE_PASSWORD'));
        $wf->soap_defencoding = 'UTF-8';
        $wf->decode_utf8 = false;

        $params = ['iddocument'=>$iddocument];
        $request = $wf->call('viewDocumentData', $params);

        return $request;
    }
    public function GetDocumentType($encoded_string)
    {
      $filedata = base64_decode($encoded_string);
      $f = finfo_open();
      $mime_type = finfo_buffer($f, $filedata, FILEINFO_MIME_TYPE);
      return $mime_type;
    }

    public function GetDocumentBySolicitud($solicitud)
    {
        $query = env('QUERY_SEARCH_DOCUMENTS_BY_SOLICITUD');
        $query = str_replace('$solicitud',$solicitud, $query);
        return $this->select($query);
    }

    public function filterByQr($documents)
    {
        $filtered = [];
        foreach ($documents as $document)
        {
            if (strpos($document['pdfname'], '_QR') !== false)
            {
                array_push($filtered,$document);
            }
        }
        if(count($filtered) == 0){
            return $documents;
        }
        return $filtered;
    }
}
