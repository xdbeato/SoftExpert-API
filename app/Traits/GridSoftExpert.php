<?php

namespace App\Traits;

/**
 *
 */
trait GridSoftExpert
{
    /**
     * Description: Create grid's array to send.
     * @param $data is the data that containt.
     * @return Array
     */
    public function arrayGrid($data)
    {
        foreach ($data as $key => $arrayValue) {
            $c = 0;
            foreach ($arrayValue as $id => $value) {
                $EntityAttribute['EntityAttribute'][$c] = array('EntityAttributeID' => $value['key'], 'EntityAttributeValue' => $value['value']);
                $c++;
            }
            $EntityAttributeList[$key]['EntityAttributeList'] = $EntityAttribute;
        }
        $array['EntityRecord'] = $EntityAttributeList;

        return $array;
    }

    /**
     * Description: Build param of GRID to send to SoftExperts.
     * @param $relationship is the relationship of the GRID IDENTIFICATOR IN SOFTEXPERTS.
     * @param $MainEntityID is the Entity id in SOFTEXPERTS.
     * @param $WorkflowID is the WorkFlow id in SOFTEXPERTS.
     * @param $data is the data with needed structure.
     * @return $entityParamGrid is the succesfully build of params to send data.
     */
    public function buildParamToSendGrid($relationship, $MainEntityID, $WorkflowID, $data)
    {
        $entityParamGrid = array('WorkflowID' => $WorkflowID,
            'MainEntityID' => $MainEntityID,
            'ChildRelationshipID' => $relationship,
            'EntityRecordList' =>$data,
        );

        return $entityParamGrid;
    }

}
