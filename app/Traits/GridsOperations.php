<?php namespace App\Traits;
use DB;
trait GridsOperations
{

    protected $GridFormResult = [];
    protected $rules;

    public function GenerateGridData($fields)
    {
        $arrayseccion = [];
        $arrayradios = [];
        $arrayinput = [];
        $arraycheck = [];
        $arraylista = [];
        $arrayfile = [];
        $arrayGrids = [];
        $arraytextarea = [];
        $arraygroup = [];
        $arrayFechas = [];
        $arrayHoras = [];
        $arrayComplete = [];
        $arrayTitle = [];

        foreach ($fields as $key => $value) {
            //separa los tipos de campos
            switch ($value['TIPOELEMENTOFM']) {
                case 'RADIOBOT':
                    array_push($arrayradios, $value);
                    break;
                case 'CHECK':
                    array_push($arraycheck, $value);
                    break;
                case 'INPUT':
                    array_push($arrayinput, $value);
                    break;
                case 'LISTA':
                    array_push($arraylista, $value);
                    break;
                case 'ARCHIVO':
                    array_push($arrayfile, $value);
                    break;
                case 'INPUT4000':
                    array_push($arraytextarea, $value);
                    break;
                case 'GRUPO':
                    array_push($arraygroup, $value);
                    break;
                case 'SECCION':
                    array_push($arrayseccion, $value);
                    break;
                case 'FECHA':
                    array_push($arrayFechas, $value);
                    break;
                case 'HORA':
                    array_push($arrayHoras, $value);
                    break;
                case 'TITULO':
                    array_push($arrayTitle, $value);
                    break;
                default:
                    //print_r('no existe el tipo de campo');
                    break;
            }

        }

    $dataArrays = array('Input' => $arrayinput,
                        'Radios' => $arrayradios,
                        'Check' => $arraycheck,
                        'lista' => $arraylista,
                        'textarea' => $arraytextarea,
                        'group' => $arraygroup,
                        'section' => $arrayseccion,
                        'date' => $arrayFechas,
                        'time' => $arrayHoras,
                        'title' => $arrayTitle);

        //estructura json para los input
    $this->GenerateInputForGrid($dataArrays['Input']);

    //estructura json y organiza los grupos  para los radiobutton
    $this->RadioButtonForGrid($dataArrays['Radios']);
    //estructura json para los checkbox

    $this->GenerateCheckForGrid($dataArrays['Check']);
    //estructura json para las listas
    $this->GenerateListaForGrid($dataArrays['lista']);

    //estructura para las fechas
    $this->GenerateFechaForGrid($dataArrays['date']);
    //estructura para las horas
    $this->GenerateHoraForGrid($dataArrays['time']);
    $this->GenerateSectionForGrid($dataArrays['section']);
    $this->GenerateTitleForGrid($dataArrays['title']);


    //Estrucutra para los grids

    // dd($dataArrays['grid']);
    //estructura json para los textarea
    $this->GenerateTextAreaForGrid($dataArrays['textarea']);
    //dd($this->GridFormResult);
    $group = $this->GenerateGroupForGrid($dataArrays['group']);
    //dd($group);
    $final =  $this->GridFormResult;

    $this->GridFormResult = [];

    return  $this->CreateGroupForGrid($group, $final);

    }

    public function GenerateInputForGrid($input)
    {

        if ($input) {
            foreach ($input as $key => $value) {

                $valores[$key] = array('type' => 'text', 'label' => $value['ETIQUETAEFM'], 'Mask' => $value['IDELEMENTOFM'], 'group' =>$value['GRUPO'],'orden' => $this->ChangeOrderToDecimal($value['ORDEN']), 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'required' => ($value['REQUERIDO'] === "1" ? true : false), 'enabled' => ($value['DISPONIBLE'] === "1" ? true : false), 'name' => $value['CAMPO'], 'subtype' => 'text', 'length' =>null);
            }
            $inputFinal = $valores;
            //dd($inputFinal);
            //print_r($inputFinal);
            foreach ($inputFinal as $input) {
                array_push($this->GridFormResult, $input);
            }

            //dd($this->GridFormResult);
        }
    }

    public function GenerateFechaForGrid($input)
    {
        //dd($input);
        if ($input) {
            foreach ($input as $key => $value) {

                $valores[$key] = array('type' => 'date', 'label' => $value['ETIQUETAEFM'], 'Mask' => $value['IDELEMENTOFM'], 'group' =>$value['GRUPO'],'orden' => $this->ChangeOrderToDecimal($value['ORDEN']), 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'required' => ($value['REQUERIDO'] === "1" ? true : false), 'enabled' => ($value['DISPONIBLE'] === "1" ? true : false), 'name' => $value['CAMPO'], 'subtype' => 'text');
            }
            $inputFinal = $valores;
            foreach ($inputFinal as $input) {
                array_push($this->GridFormResult, $input);
            }


        }

    }

    public function GenerateSectionForGrid($section)
    {
        //dd($section);
        if ($section) {
            $valores;
            foreach ($section as $key => $value) {
                $actual = $value['ETIQUETAEFM'];
                if($actual == "" || $actual == null)
                {
                    die(json_encode(array('error' => 'Error en la configuracion de una Seccion o FieldSet', 'field' => $value, 'detail' => 'Debe colocar un id al fieldset')));
                }
                if($value['ETIQUETAEFM'] !== null){
                $valores[$key] = array('type' => 'header','subtype'=> 'h2', 'name' => $value['IDELEMENTOFM'], 'label' => $value['ETIQUETAEFM'], 'group' =>$value['GRUPO'], 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'orden' => $this->ChangeOrderToDecimal($value['ORDEN']));
                }
            }
            //dd($valores);
            $inputFinal = $valores;
            foreach ($inputFinal as $section) {
                array_push($this->GridFormResult, $section);
            }


        }

    }
    public function GenerateTitleForGrid($title){
        //dd($title);
        if ($title) {
            $valores;
            foreach ($title as $key => $value) {
                if($value['ETIQUETAEFM'] !== null){
                $valores[$key] = array('type' => 'subtitle','subtype'=> 'h3', 'name' => $value['IDELEMENTOFM'], 'label' => $value['ETIQUETAEFM'], 'group' =>$value['GRUPO'], 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'orden' => $this->ChangeOrderToDecimal($value['ORDEN']));
                }
            }
            //dd($valores);
            $inputFinal = $valores;
            foreach ($inputFinal as $title) {
                array_push($this->GridFormResult, $title);
            }


        }
    }



    public function GenerateHoraForGrid($input)
    {
        //dd($input);
        if ($input) {
            foreach ($input as $key => $value) {

                $valores[$key] = array('type' => 'time', 'label' => $value['ETIQUETAEFM'], 'Mask' => $value['IDELEMENTOFM'], 'group' =>$value['GRUPO'],'orden' => $this->ChangeOrderToDecimal($value['ORDEN']), 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'required' => ($value['REQUERIDO'] === "1" ? true : false), 'enabled' => ($value['DISPONIBLE'] === "1" ? true : false), 'name' => $value['CAMPO'], 'subtype' => 'text');
            }
            $inputFinal = $valores;
            foreach ($inputFinal as $input) {
                array_push($this->GridFormResult, $input);
            }


        }

    }
    public function RadioButtonForGrid($data)
    {

        if ($data) {
            //declaracion de array
            $arrayRadio = $data;
            $grupo = array();
            $arrayFinal = array();
            $radioButton = array();

            //foreach para sacar los campos [radiobutton01,02,03]
            foreach ($arrayRadio as $key => $value) {
                array_push($grupo, $value['CAMPO']);
            }

            //array si campos repetidos;
            $grupoFilter = array_unique($grupo);

            //foreach de array para setear los grupos en las primeras posiciones
            foreach ($grupoFilter as $key => $value) {
                array_push($radioButton, $value);

            }

            //for que recorre todos los array de los radiobutton extraidos de la DB
            for ($i = 0; $i < count($radioButton); $i++) {

                //compara que array petenece a su grupo
                foreach ($arrayRadio as $key => $value) {
                    if ($radioButton[$i] == $value['CAMPO']) {
                        array_push($arrayFinal, $arrayRadio[$key]);

                    }

                }
                //crear un array con todos los grupos y depues limpia la variable que traer un array
                $radio[$radioButton[$i]] = $arrayFinal;
                $arrayFinal = array();

            }
            $inputFinal =  $this->GenerateRadioForGrid($radio);
            foreach ($inputFinal as $radio) {
                array_push($this->GridFormResult, $radio);
            }
        }
        else
        {
            return array();
        }
    }

    public function GenerateCheckForGrid($check)
    {
        //dd($check);
        if ($check) {
            foreach ($check as $key => $value) {
                //get rule with var
                if(count($this->rules) > 0)
                {
                    $data =  explode("K",$value['IDELEMENTOFM']);
                    $rules = $this->rules[0]->rules? $rules = $this->rules[0]->rules : $rules ='';
                    $rule = $this->AllRules($rules, $data[0]);
                }
                else
                {
                    $rule = '';
                }

                $maskRule = explode("K",$value['IDELEMENTOFM']);

                $valores[0] = array('label' => $value['ETIQUETAEFM'],  'value' => $value['CAMPO'], 'rule'=>$rule);

                $checkFinal[$key] = array('type' => 'checkbox-group', 'label' => $value['ETIQUETAEFM'], "mask"=>null, 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'enabled' => ($value['DISPONIBLE'] === "1" ? true : false), 'group' =>$value['GRUPO'],'orden' => $this->ChangeOrderToDecimal($value['ORDEN']), 'name' => $value['CAMPO'], 'values' => $valores);

            }

            foreach ($checkFinal as $check) {
                array_push($this->GridFormResult, $check);
            }

        }

    }

         //ordena el array en orden ascendente
    public function ordernaGrid($array) {
        return function ($a, $b) use ($array) {
            return strnatcmp($a[$array], $b[$array]);
        };
    }
    public function GenerateListaForGrid($lista)
    {
        //dd($lista);
        if ($lista) {
            foreach ($lista as $key => $value) {

                $persistencia_ids = explode('U',$value['CAMPO']);
                if(count($persistencia_ids) < 2)
                {
                    die(json_encode(array('error' => 'Error en la configuración de la lista.', 'field' => $value, 'detail' => 'Debe colocar los campos de persistencia en el id de la lista.')));
                }
                $persist_value = $persistencia_ids[0];
                $persist_label = $persistencia_ids[1];
                $father_id = null;
                if(count($persistencia_ids) == 3){
                    $father_id = $persistencia_ids[2];
                }

                //sacar el nombre del padre
                $query = $value['QRY_LISTAVALOR'];

                preg_match_all("/'([^']+)'/", $value['QRY_LISTAVALOR'], $matches);
                $NombrePadre = end($matches[0]);
                $nombre = str_replace("'", "", $NombrePadre);
                // sacar el nombre del select actual
                $NombreActual = reset($matches[0]);
                $actual = str_replace("'", "", $NombreActual);
                //print_r($query);
                //Pregunta si la lista actual es una Provincia, Municipio, Sector OJO QUITAR - CUANDO ESTE DISPONIBLE PARA EL PORTAL
                if($actual == "provincia" || $actual == "municipio" || $actual == "sector")
                {

                    //Debe generar un select sin sus values; serán usados para el portal
                    $options = null;
                    $listaFinal[$key] = array('type' => 'select', 'father_id' => $father_id,'entity'=> $actual, 'group' =>$value['GRUPO'], 'label_persist' => $persist_label, 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'required' => ($value['REQUERIDO'] === "1" ? true : false),'enabled' => ($value['DISPONIBLE'] === "1" ? true : false), 'label' => $value['ETIQUETAEFM'], 'orden' => $this->ChangeOrderToDecimal($value['ORDEN']),  'name' => $persist_value, 'values' => 'null', 'data_portal' =>true,'select_portal_type' =>$actual);

                }
                else
                {

                    if( strpos($value['QRY_LISTAVALOR'], 'DTIPOCLASPADRE') === false  ){

                        $options = $this->selectForGrids($value['QRY_LISTAVALOR']);
                        $listaFinal[$key] = array('type' => 'select', 'father_id' => $father_id,'entity'=> $actual, 'group' =>$value['GRUPO'], 'label_persist' => $persist_label, 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'required' => ($value['REQUERIDO'] === "1" ? true : false),'enabled' => ($value['DISPONIBLE'] === "1" ? true : false), 'label' => $value['ETIQUETAEFM'], 'orden' => $this->ChangeOrderToDecimal($value['ORDEN']),  'name' => $persist_value, 'values' => $options,'data_portal' =>false,'select_portal_type' =>null);

                    }else{

                            if(strpos($value['QRY_LISTAVALOR'], 'pais') === false ){
                                    $options = $this->selectForGrids($value['QRY_LISTAVALOR']);
                                    $listaFinal[$key] = array('type' => 'select','father_id' => $father_id, 'entity'=> $actual, 'group' =>$value['GRUPO'], 'label_persist' => $persist_label,'father' => $nombre, 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'required' => ($value['REQUERIDO'] === "1" ? true : false),'enabled' => ($value['DISPONIBLE'] === "1" ? true : false), 'label' => $value['ETIQUETAEFM'], 'orden' => $this->ChangeOrderToDecimal($value['ORDEN']), 'name' => $persist_value, 'values' => $options,'data_portal' =>false,'select_portal_type' =>null);
                                    //$listaFinal[$key] = array('type' => 'select', 'required' => ($value['REQUERIDO'] === "1" ? true : false), 'label' => $value['ETIQUETAEFM'],'father' =>$nombre, 'name' => $value['CAMPO'], 'data' => $options);
                            } else {
                                $options = $this->selectForGrids($value['QRY_LISTAVALOR']);
                                    $listaFinal[$key] = array('type' => 'select', 'father_id' => $father_id,'entity'=> $actual, 'group' =>$value['GRUPO'],'label_persist' => $persist_label, 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'required' => ($value['REQUERIDO'] === "1" ? true : false),'enabled' => ($value['DISPONIBLE'] === "1" ? true : false), 'label' => $value['ETIQUETAEFM'], 'orden' => $this->ChangeOrderToDecimal($value['ORDEN']),  'name' => $persist_value, 'values' => $options,'data_portal' =>false,'select_portal_type' => null);
                                    //$listaFinal[$key] = array('type' => 'select', 'required' => ($value['REQUERIDO'] === "1" ? true : false), 'label' => $value['ETIQUETAEFM'],'father' =>$nombre, 'name' => $value['CAMPO'], 'data' => $options);
                            }

                    }

                }


            }

            foreach ($listaFinal as $lista) {
                array_push($this->GridFormResult, $lista);

            }

            //dd($listaFinal);

        }
    }

    public function GenerateRadioForGrid($radio)
    {
        $c = 0;
        foreach ($radio as $key => $value) {

            for ($i = 0; $i < count($value); $i++) {
               // dd($radio);
                //function of rule's radio***************************
                if(strpos($value[$i]['IDELEMENTOFM'], 'K') !== false)
                {
                //explode of value and var
                $data = explode("K", $value[$i]['IDELEMENTOFM']);
                  //get rule with var
                if(count($this->rules) > 0)
                {
                    $rules = $this->rules[0]->rules? $rules = $this->rules[0]->rules : $rules ='';
                    $rule = $this->AllRules($rules, $data[1]);
                }
                else
                {
                    $rule = '';
                }

                $valores[$i] = array('label' => $value[$i]['ETIQUETAEFM'], 'value' => $data[0], 'rule' => $rule);
                }else
                {
                    $valores[$i] = array('label' => $value[$i]['ETIQUETAEFM'], 'value' => $value[$i]['IDELEMENTOFM']);
                }
            }
            $RadioGroup[$key] = $valores;

            $radioFinal[$c] = array('type' => 'radio-group', 'group' =>$value[0]['GRUPO'], 'label' => $value[0]['NOMBRECAMPO'], 'name' => $key, 'hidden' => ($value[0]['VISIBLE'] === "1" ? true : false), 'enabled' => ($value[0]['DISPONIBLE'] === "1" ? true : false), 'orden' => $this->ChangeOrderToDecimal($value[0]['ORDEN']), 'values' => $RadioGroup[$key]);
            $c++;

            $valores = array();
        }

        return $radioFinal;
    }

    public function GenerateListForGrid($lista)
    {
        if ($lista)
        {
            foreach ($lista as $key => $value)
            {
                $persistencia_ids = explode('U',$value['CAMPO']);
                $persist_value = $persistencia_ids[0];
                $persist_label = $persistencia_ids[1];
                $father_id = null;
                if(count($persistencia_ids) == 3){
                    $father_id = $persistencia_ids[2];
                }
                //sacar el nombre del padre
                preg_match_all("#'(\w+)'#", $value['QRY_LISTAVALOR'], $matches);
                $NombrePadre = end($matches[0]);
                $nombre = str_replace("'", "", $NombrePadre);
                // sacar el nombre del select actual
                $NombreActual = reset($matches[0]);
                $actual = str_replace("'", "", $NombreActual);

                if( strpos($value['QRY_LISTAVALOR'], 'DTIPOCLASPADRE') === false  )
                {
                    $options = $this->selectForGrids($value['QRY_LISTAVALOR']);
                    $listaFinal[$key] = array('type' => 'select','father_id' => $father_id, 'entity'=> $actual, 'group' =>$value['GRUPO'], 'label_persist' => $persist_label, 'required' => ($value['REQUERIDO'] === "1" ? true : false), 'label' => $value['ETIQUETAEFM'], 'orden' => $this->ChangeOrderToDecimal($value['ORDEN']),  'name' => $persist_value, 'values' => $options);

                }else
                {

                    if(strpos($value['QRY_LISTAVALOR'], 'pais') === false )
                    {
                        $options = $this->selectForGrids($value['QRY_LISTAVALOR']);
                        $listaFinal[$key] = array('type' => 'select', 'father_id' => $father_id,'entity'=> $actual, 'group' =>$value['GRUPO'], 'label_persist' => $persist_label,'father' => $nombre, 'required' => ($value['REQUERIDO'] === "1" ? true : false), 'label' => $value['ETIQUETAEFM'], 'orden' => $this->ChangeOrderToDecimal($value['ORDEN']), 'name' => $persist_value, 'values' => $options);
                                //$listaFinal[$key] = array('type' => 'select', 'required' => ($value['REQUERIDO'] === "1" ? true : false), 'label' => $value['ETIQUETAEFM'],'father' =>$nombre, 'name' => $value['CAMPO'], 'data' => $options);
                    } else
                    {
                        $options = $this->selectForGrids($value['QRY_LISTAVALOR']);
                        $listaFinal[$key] = array('type' => 'select', 'father_id' => $father_id,'entity'=> $actual, 'group' =>$value['GRUPO'],'label_persist' => $persist_label, 'required' => ($value['REQUERIDO'] === "1" ? true : false), 'label' => $value['ETIQUETAEFM'], 'orden' => $this->ChangeOrderToDecimal($value['ORDEN']),  'name' => $persist_value, 'values' => $options);
                                //$listaFinal[$key] = array('type' => 'select', 'required' => ($value['REQUERIDO'] === "1" ? true : false), 'label' => $value['ETIQUETAEFM'],'father' =>$nombre, 'name' => $value['CAMPO'], 'data' => $options);
                    }

                }

            }
        }
        return $listaFinal;

    }

    public function GenerateTextAreaForGrid($area)
    {
        if ($area) {
            foreach ($area as $key => $value) {

                $valores[$key] = array('type' => 'textarea', 'label' => $value['ETIQUETAEFM'], 'group' =>$value['GRUPO'],'orden' => $value['ORDEN'], 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'required' => ($value['REQUERIDO'] === "1" ? true : false),'enabled' => ($value['DISPONIBLE'] === "1" ? true : false), 'name' => $value['CAMPO'], 'length' => null);
            }
            $inputFinal = $valores;
            foreach ($inputFinal as $area) {
                array_push($this->GridFormResult, $area);
            }


        }
    }
    public function selectForGrids($query)
    {
        //elimina la ultima linea del query
        $query = strtoupper($query);
        $query_parts = explode('FROM',$query);

        $first_line_query = "SELECT BIDCLASIF, CTITULOCLAS, EIDCLASPADRE";
        $second_line_query = $query_parts[1];

        $query = $first_line_query . ' FROM '. $second_line_query;

        $order_by = $this->searchOrderBy($query);

        $query = strtoupper($query);
        $valor = explode("AND", $query);

        if(!array_key_exists('1', $valor))
        {
            $final_query = $query;
        }else
        {
            $final_query = $valor[0] .'and'. $valor[1];
        }

        $final_query = strtoupper($final_query);

        if(!preg_match('/\ORDER BY\b/',$final_query))
        {
            $final_query =  $final_query . $order_by;
        }
        $options = [];
        $select = $this->select($final_query);

        if (!empty($select)) {
            foreach ($select as $option) {
                if(array_key_exists('EIDCLASPADRE', $option)){
                    array_push($options, array('label' => $option->CTITULOCLAS, 'rule' =>"", 'value' => $option->BIDCLASIF,'father' =>$option->EIDCLASPADRE));
                }else{
                    array_push($options, array('label' => $option->CTITULOCLAS,'rule' =>"", 'value' => $option->BIDCLASIF));
                }

            }
        }

        return $options;
    }

    public function CreateGroupForGrid($group, $data)
    {
        $array = array();
        $arrayGroup = [];
        $arrayHeader = array();
        $json = array();

        if(count($group) < 1)
        {
            die(json_encode(array('error' => 'Error en la configuración de grupos en formulario del grid', 'field' => null, 'solution' => 'No existe al menos un fieldset en el grid con la configuración de grp01, grp02...')));

        }
        foreach ($group as $key => $value)
        {
            $array = [];
            $arrayHeader = array('type' => 'header', 'subtype'=> 'h1','name' =>$value['name'],'label' => $value['label'], 'hidden'=>$value['hidden'], 'orden' =>$value['orden'] );
            $array[0] = $arrayHeader;
            $c = 1;
            foreach ($data as $key2 => $value2)
            {
                $idGroup = substr($value2['group'], 0, 5);
                if($idGroup == "" || $idGroup == null)
                {
                    die(json_encode(array('error' => 'Error en la configuración del campo.', 'field' => $value2, 'detail' => 'Este campo del GRID no tiene grupo asignado')));
                }

                if($key == $idGroup){

                    $array[$c] = $value2;
                    $c++;

                }

            }
            usort($array,  $this->ordernaGrid('orden'));
            array_push( $arrayGroup, $array);
        }


        return $arrayGroup[0];

    }


    public function GenerateGroupForGrid($group)
    {
        $valores = [];
        $header = [];

        if ($group) {
            foreach ($group as $key => $value) {
                $id = substr($value['IDELEMENTOFM'], 0,5);
                if($id == "" || $id == null)
                {
                    die(json_encode(array('error' => 'Error en la configuracion del Grupo','field' => $value, 'detail' =>'No se ha informado un id para este grupo o fieldset')));
                }

                if (!array_key_exists($id, $valores)) {

                    $valores[$id] =  array('label' => $value['ETIQUETAEFM'], 'name' => $value['IDELEMENTOFM'],'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'orden' => $this->ChangeOrderToDecimal($value['ORDEN']));

                }else {

                    if($value['ETIQUETAEFM'] !== null){
                        $header[$key] =  array('type'=> 'header', 'name' => $value['IDELEMENTOFM'], 'subtype'=>'h1', 'label' => $value['ETIQUETAEFM'], 'group'=> $id, 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'orden' => $this->ChangeOrderToDecimal($value['ORDEN']));

                    }


                }

            }

            if($header != []){
                $inputFinal = $header;
            foreach ($inputFinal as $headers) {
                array_push($this->result, $headers);
                //dump($headers);
            }
            }

           // dump($valores);
            return $valores;


        }

    }


    public function GenerateDateForGrid($input)
    {
    }
}
