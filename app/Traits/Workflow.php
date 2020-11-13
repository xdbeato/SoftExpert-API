<?php

namespace App\Traits;

/**
 *
 */
trait Workflow
{
    /**
     * Description: Create grid's array to send.
     * @param $entityParamGrid
     * @return array
     */
    public function newChildEntityRecord($entityParamGrid)
    {

        $wf = new \nusoap_client(env('URL_SOFTEXPERT'), false);//Esta url es la de la webservice de Workflow, se captura desde un archivo de configuracion (.env)
        $wf->setCredentials(env('SE_USER'), env('SE_PASSWORD'));
        $wf->soap_defencoding = 'UTF-8';
        $wf->decode_utf8 = false;
        //dd($entityParamGrid);
        $response = $wf->call('newChildEntityRecordList', $entityParamGrid);

        $response = ['Status' => $response['Status'], 'Detail' => utf8_encode($response['Detail'])];

        return $response;
    }


    public function newWorkFlow($process_id)
    {
        $wf = new \nusoap_client(env('URL_SOFTEXPERT'), false);//Esta url es la de la webservice de Workflow, se captura desde un archivo de configuracion (.env)
        $wf->setCredentials(env('SE_USER'), env('SE_PASSWORD'));
        //dd(env('SE_USER'));
        $wf->soap_defencoding = 'UTF-8';
        $wf->decode_utf8 = false;
        //dd($entityParamGrid);
        $entityParam = ['ProcessID' => $process_id, 'WorkflowTitle' => 'Prueba de Formulario','UserID'=>env('SE_USER')];
        return $wf->call('newWorkflow', $entityParam);

    }

    /**
     * @param string $workflowid
     * @param string $entity
     * @param array $fields
     * @param boolean $formTest
     * @return array
     */
    public function editEntityRecord(string $workflowid,$entity,array $fields, $formTest = false)
    {
        $wf = new \nusoap_client(env('URL_SOFTEXPERT'), false);//Esta url es la de la webservice de Workflow, se captura desde un archivo de configuracion (.env)
        $wf->setCredentials(env('SE_USER'), env('SE_PASSWORD'));
        $wf->soap_defencoding = 'UTF-8';
        $wf->decode_utf8 = false;

        $EntityAttributeList['EntityAttribute'] = array();
        foreach($fields as $field)
        {
            $EntityAttribute = ['EntityAttributeID' => $field['key'], 'EntityAttributeValue' => $field['value']];
            array_push($EntityAttributeList['EntityAttribute'], $EntityAttribute);
        }
        $entityParam = ['WorkflowID' => $workflowid, 'EntityID' => $entity,'EntityAttributeList' =>$EntityAttributeList];
        $response = $wf->call('editEntityRecord', $entityParam);
        //dd($response);

        if($response['Status'] == "success" && $formTest == true)
        {
            $this->deleteWorkflowAndData($workflowid);
        }
        return ['Status' => $response['Status'], 'Code' => $response['Code'], 'Detail' => utf8_decode($response['Detail'])];

    }

    /**
     * @param string $WorkflowID
     * @param string $activity
     * @return mixed
     */
    public function executeActivity(string $WorkflowID, string $activity)
    {
        $wf = $this->getClient();
        $params = ['WorkflowID' =>$WorkflowID, 'ActivityID' =>$activity,'ActionSequence' => 1, 'UserID' =>'','ActivityOrder' =>''];
        //dd($params);
        return  $wf->call('executeActivity',$params);

    }


    private function getClient() : \nusoap_client
    {
        $wf = new \nusoap_client(env('URL_SOFTEXPERT'), false);//Esta url es la de la webservice de Workflow, se captura desde un archivo de configuracion (.env)
        $wf->setCredentials(env('SE_USER'), env('SE_PASSWORD'));
        $wf->soap_defencoding = 'UTF-8';
        $wf->decode_utf8 = false;

        return $wf;
    }
    public function deleteWorkflowAndData($WorkflowID)
    {
        $wf = new \nusoap_client(env('URL_SOFTEXPERT'), false);//Esta url es la de la webservice de Workflow, se captura desde un archivo de configuracion (.env)
        $wf->setCredentials(env('SE_USER'), env('SE_PASSWORD'));
        $wf->soap_defencoding = 'UTF-8';
        $wf->decode_utf8 = false;

        $params = ['WorkflowID' => $WorkflowID,'Explanation' => 'Este era un workflow para prueba de formulario', 'UserID' =>env('SE_USER')];
        $wf->call('cancelWorkflow',$params);
    }

    public function newAssocDocument($documentid,$workflowid,$activityid)
    {
        $wf = new \nusoap_client(env('URL_SOFTEXPERT'), false);//Esta url es la de la webservice de Workflow, se captura desde un archivo de configuracion (.env)
        $wf->setCredentials(env('SE_USER'), env('SE_PASSWORD'));
        $wf->soap_defencoding = 'UTF-8';
        $wf->decode_utf8 = false;

    }
    public function newAssocDocumentMultiple($documents,$workflowid,$activityid)
    {
        $wf = new \nusoap_client(env('URL_SOFTEXPERT'), false);//Esta url es la de la webservice de Workflow, se captura desde un archivo de configuracion (.env)
        $wf->setCredentials(env('SE_USER'), env('SE_PASSWORD'));
        $wf->soap_defencoding = 'UTF-8';
        $wf->decode_utf8 = false;
        $responses_text = '';
        $success_count = 0;
        $fail_count = 0;

        foreach($documents as $document)
        {
            if($document == "")
            {
                continue;
            }
            $params = ['WorkflowID'=>$workflowid,'ActivityID'=>$activityid,'DocumentID'=>$document];
            $response = $wf->call('newAssocDocument',$params);
            $response_text = "Status: ".$response["Status"]." Message: ".$response["Detail"];
            $responses_text .= $response_text."\n";


            if($response["Status"] == 'SUCCESS')
            {
                $success_count = $success_count + 1;
            }
            else
            {
                $fail_count = $fail_count + 1;
            }
        }
        $responses_text = utf8_encode($responses_text);
        $status = 'success';
        if($fail_count > 0)
        {
            $status = 'failure';
        }
        return ['status' => $status, 'message' => $responses_text];


    }

    public function editChildEntityRecord($WorkflowID,$MainEntityID,$ChildRelationshipID,$ChildRecordOID,$EntityAttributeList)
    {
        $wf = new \nusoap_client(env('URL_SOFTEXPERT'), false);//Esta url es la de la webservice de Workflow, se captura desde un archivo de configuracion (.env)
        $wf->setCredentials(env('SE_USER'), env('SE_PASSWORD'));
        $wf->soap_defencoding = 'UTF-8';
        $wf->decode_utf8 = false;

        $params = ['WorkflowID' => $WorkflowID,'MainEntityID' => $MainEntityID,'ChildRelationshipID'=>$ChildRelationshipID,'ChildRecordOID' =>$ChildRecordOID,'EntityAttributeList' =>$EntityAttributeList];

        $response =  $wf->call('editChildEntityRecord',$params);
        return ['Status' => $response['Status'],'Detail' => utf8_decode($response['Detail'])];
    }
}
