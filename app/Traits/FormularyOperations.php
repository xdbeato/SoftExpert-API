<?php

namespace App\Traits;

trait FormularyOperations {

    public function BuildFormForTest($formulary_data)
    {
        $fields = [];
        foreach($formulary_data as $field)
        {
            //dump($field);
            try {
                if($field['type'] =='text' || $field['type'] == "textarea")
                {
                    if(array_key_exists('Mask', $field))
                    {
                        $value = $this->getValuedMasked($field['Mask']);
                    }
                    else 
                    {
                        $value = 'Lorem ipsum dolor sit amet';
                    }
                    $current = ['key' =>$field['name'], 'value' =>$value,'type' =>$field['type'],'label' =>$field['label']];
                    array_push($fields, $current);
                }
                if($field['type'] == 'select')
                {
                    $current = ['key' =>$field['name'], 'value' => 'Lorem ipsum dolor sit amet','type' =>$field['type'].'-campo-id-persistencia', 'label' => $field['label']];
                    $persist = ['key' =>$field['label_persist'], 'value' => 'Lorem ipsum dolor sit amet','type'=>$field['type'].'-campo-valor-persistencia','label' =>$field['label']];
                    $father = $field['father_id'];
                    array_push($fields,$current,$persist);
                    if($father !=null)
                    {
                        $father = ['key' =>$field['father_id'], 'value' => 'Lorem ipsum dolor sit amet','type' =>$field['type'].'-campo-id-persistencia-padre', 'label' => "Campo de persitencia Padre"];
                        array_push($fields,$father);
                    }
                }
                if($field['type'] == "radio-group")
                {
                    $value = $field['values'][0]['value'];
                    $current = ['key' => $field['name'], 'value' => $value,'type'=>$field['type'],'label'=>$field['label']];
                    array_push($fields,$current);
                }
                if($field['type'] =='date')
                {
                    $current = ['key' => $field['name'], 'value' => '1995-01-17','type'=>$field['type'],'label'=>$field['label']];
                    array_push($fields,$current);
                }
            } catch (Exception $e) {
               
                echo 'Excepción capturada: ',  $e->getMessage(), "\n";
            }
           

        }

        return $fields;
    }

    private function getValuedMasked($maskid)
    {
        switch ($maskid) {
            case '0':
                return "000-0000000-0";
                break;
            case '1':
                return "000000000";
                break;
            case '2':
                return "(000)-000-0000";
                break;
            case '3':
                return "(000)-000-0000";
                break;
            case '4':
                return "00000";
                break;
            case '5':
                return "prueba@prueba.com";
                break;
            case '6':
                return 10;
                break;
            case '7':
                return "Lorem ipsum dolor sit amet";
                break;
            case '8':
                return "999999999999999-9999";
                break;
            case '9':
                return "000000";
                break;
            case '10':
                return "1995-01-17";
                break;
            case '11':
                return "12:12";
                break;
            case '12':
                return 1.25;
                break;
            case '13':
                return "1256";
                break;
            case '14':
                return "00000000";
                break;
            case '15';
                return 300;
                break;
            case '16':
                return 1500;
                break;
            case '17':
                return "-00.000000";
                break;
            case '18':
                return "00.000000";
                break;
            case '20':
                return "000-0000000-0";
                break;
            case '21':
                return 1212;
                break;
            case '23':
                return "1995-01-17";
                break;
            case '24':
                return "1995-01-17";
                break;
            case '27':
                return 12;
                break;
            case '28': 
                return 12;
                break;
            default:
                return 1;
                break;
        }
    }

}