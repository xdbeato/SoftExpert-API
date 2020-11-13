<?php

namespace App\Http\Controllers;

use DB;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Traits\FormQueryExecution;
use App\Traits\GridsOperations;
use App\Traits\DataQuery;

class ReaderController extends Controller
{
    use  FormQueryExecution;
    use GridsOperations;
    use DataQuery;
    protected $result = [];
    protected $rules;

    public function getform(Request $request)
    {
        $rows = $this->executeQuery($request->idformulario);
        if($rows == null)
        {
            die(json_encode(array('error' => 'Formulario inexistente', 'field' => null, 'solution' => 'Ingrese un id de formulario existente en Softexpert')));
        }
        if($request->env =='debug')
        {
            return $rows;
        }

        $this->rules = [];
        $json=[];

        $array = json_decode(json_encode($rows), true);
        $dataArrays = $this->GetData($array);

        if($request->env == 'preview'){
            return $dataArrays;
        }


        foreach ($array as $key => $value) {
            if($value['ENTIDAD'] !== 'ENTIDAD'){
                $entityId = $value;
            }
        }
        //format json for pagination

        for($c=0; $c<count($dataArrays); $c++ ){
            $data = json_encode($dataArrays[$c]);
            array_push($json, $data);

        }


        if($request->FormBuilder == true)
        {
            return array('form'=> $json, 'entity'=> $entityId['ENTIDAD']);
        }
       //  return $json;//


        $client = new Client(['headers' => ['X-Authorization' => 'XiS9cuDMlRP0YtMsXPNepZFti5jqeoQdm0LbnZh8IMvZmF118LqCNSSj6CDVnYPv'],"verify" => false]);
        $urlServer = '';

       switch ($request->env) {
           case 'pre-produccion':
            $urlServer = env('URL_BACKEND_PREPRODUCCION');
               break;
           case 'prueba':
           $urlServer = env('URL_BACKEND_PRUEBA');
               break;
            case 'produccion':
                $urlServer = env('URL_BACKEND_PRODUCCION');
               break;
            case 'desarrollo':
            $urlServer = env('URL_BACKEND_DESARROLLO');
                    break;

           default:
               return array('code'=>'404', 'message'=> 'url backend api not found');
               break;
       }


       $rq = $client->post($urlServer, [
        'headers' => [
            'Accept'     => 'application/json'
        ]
        ,'json' => [
               'name' => $request->idformulario,
               'fields' => $json,
               'service_id' => $request->service_id,
               'institution_id' => $request->institution_id,
               'version' => $request->version,
               'entity' => $entityId['ENTIDAD'],

           ],
       ]);
//TESTING GIT
        return $rq->getBody()->getContents() .' || server: '.$request->env;

    }

    public function GetData($data)
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


        foreach ($data as $key => $value) {
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
                case 'GRID':
                    array_push($arrayGrids, $value);
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

        //array con los campos separados

        $dataArrays = array('Input' => $arrayinput,
                            'Radios' => $arrayradios,
                            'Check' => $arraycheck,
                            'lista' => $arraylista,
                            'File' => $arrayfile,
                            'textarea' => $arraytextarea,
                            'group' => $arraygroup,
                            'section' => $arrayseccion,
                            'grid' => $arrayGrids,
                            'date' => $arrayFechas,
                            'time' => $arrayHoras,
                            'title' => $arrayTitle);




        //estructura json para los input
         $this->GenerateInput($dataArrays['Input']);
        //estructura json para  file
         $this->GenerateFile($dataArrays['File']);
        //estructura json y organiza los grupos  para los radiobutton
         $this->RadioButton($dataArrays['Radios']);
        //estructura json para los checkbox

         $this->GenerateCheck($dataArrays['Check']);
        //estructura json para las listas
         $this->GenerateLista($dataArrays['lista']);

         //estructura para las fechas
         $this->GenerateFecha($dataArrays['date']);
         //estructura para las horas
         $this->GenerateHora($dataArrays['time']);
        // dd($dataArrays);
         $this->GenerateSection($dataArrays['section']);
         $this->GenerateTitle($dataArrays['title']);

        //Estrucutra para los grids

        // dd($dataArrays['grid']);
        //estructura json para los textarea
        $this->GenerateArea($dataArrays['textarea']);

        $this->GenerateGrids($dataArrays['grid']);


        $group = $this->GenerateGroup($dataArrays['group']);
        // uniendo los datos en un solo array


    if($group == null)
    {
        die(json_encode(array('error' => 'Error en configuracion de los grupos', 'field' => null, 'solution' => 'Debe exisitr al menos un grupo con id grp en el formulario. Es probable que los fieldsets tengan el prefijo FS')));
    }
        //dd($result);
       return  $this->CreateGroup($group, $this->result);
    }



    public function CreateGroup($group, $data)
    {
        $array = array();
        $arrayGroup = [];
        $arrayHeader = array();
        $json = array();

        if(count($group) < 1)
        {
            die(json_encode(array('error' => 'Error en la configuración de grupos', 'field' => $value2, 'solution' => 'No existe al menos un fieldset con la configuración de grp01, grp02...')));
        }

        foreach ($group as $key => $value)
        {
            $array = [];

            $arrayHeader = array('type' => 'header', 'subtype'=> 'h1','name' =>$value['name'],'label' => $value['label'], 'hidden'=>$value['hidden'], 'orden' =>$value['orden'] );
            $initrules  = ['type' => 'rules', 'rules' => [],'orden' =>$value['orden'].'100227100025' ];
            $array[0] = $arrayHeader;

            $c = 1;
            foreach ($data as $key2 => $value2)
            {
                $idGroup = substr($value2['group'], 0, 5);
                if($idGroup == "" || $idGroup == null)
                {
                    die(json_encode(array('error' => 'Error en la configuración del campo.', 'field' => $value2, 'solution' => 'Debe colcoar este campo dentro de un grupo que esté debidamente configurado para el portal.')));
                }
                if($key == $idGroup){

                    $array[$c] = $value2;
                    $c++;

                }
            }
            $array[$c+1] = $initrules;


            usort($array,  $this->ordena('orden'));

         array_push( $arrayGroup, $array);

        }




        return $arrayGroup;

    }

    public function RadioButton($data)
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

            //genera la extructura del json con los array
            $inputFinal =  $this->GenerateRadio($radio);
            foreach ($inputFinal as $radio) {
                array_push($this->result, $radio);
            }
        } else {
            return array();
        }

    }



    public function GenerateRadio($radio)
    {
        $c = 0;
        foreach ($radio as $key => $value) {

            for ($i = 0; $i < count($value); $i++) {
                //function of rule's radio***************************
                if(strpos($value[$i]['IDELEMENTOFM'], 'K') !== false){
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
                }else{
                    $id =  $value[$i]['IDELEMENTOFM'];
                    if($id == "" || $id == null){
                        die(json_encode(array('error' => 'Error en configuración de Radiobuton', 'field' => $value[$i], 'solution' => 'No se ha informado el valor del radiobutton. Este debe ser colocado en el id del radiobuton en el Formulario')));
                    }
                    $valores[$i] = array('label' => $value[$i]['ETIQUETAEFM'], 'value' => $value[$i]['IDELEMENTOFM']);
                }

            }
            $RadioGroup[$key] = $valores;

            $radioFinal[$c] = array('type' => 'radio-group', 'group' =>$value[0]['GRUPO'], 'label' => $value[0]['NOMBRECAMPO'], 'name' => $key, 'hidden' => ($value[0]['VISIBLE'] === "1" ? true : false), 'enabled' => ($value[0]['DISPONIBLE'] === "1" ? true : false), 'required' => true, 'orden' => $this->ChangeOrderToDecimal($value[0]['ORDEN']), 'values' => $RadioGroup[$key]);
            $c++;

            $valores = array();
        }
        return $radioFinal;
    }

    public function AllRules($rules = '', $var = '')
    {
        //array of rules
        if($var){
            $ArrayRules  = explode(";", $rules);
            //get rule of var
            foreach ($ArrayRules as $key => $value) {
                $varAndRule = explode("=", $value);
                    if(trim($varAndRule[0]) === $var){
                        return trim($varAndRule[1]);
            }

       }
        }else{
            return '';
        }

    }

    public function GenerateInput($input)
    {
        if ($input) {
            foreach ($input as $key => $value) {
                $mask = substr($value['IDELEMENTOFM'],0,2);
                $maskParam = substr($value['IDELEMENTOFM'],2);
                $length = $this->GetInputLength($value);
                if($value['ETIQUETAEFM'] == "")
                {
                    die(json_encode(array('error' => 'Error en la configuracion del campo input','field' => $value, 'solution' =>'No se ha colocado un nombre al campo en el formulario.')));
                }
                $valores[$key] = array('type' => 'text', 'label' => $value['ETIQUETAEFM'], 'Mask' => $mask, 'MaskParam'=>$maskParam, 'group' =>$value['GRUPO'],'orden' => $this->ChangeOrderToDecimal($value['ORDEN']), 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'required' => ($value['REQUERIDO'] === "1" ? true : false), 'enabled' => ($value['DISPONIBLE'] === "1" ? true : false), 'name' => $value['CAMPO'], 'subtype' => 'text', 'length' =>$length,'ruleIn' => '', 'ruleOut' => '');
            }
            $inputFinal = $valores;
            foreach ($inputFinal as $input) {
                array_push($this->result, $input);
            }
        }

    }

    public function GenerateHora($input)
    {
        if ($input) {
            foreach ($input as $key => $value) {
                $mask = substr($value['IDELEMENTOFM'],0,2);
                $maskParam = substr($value['IDELEMENTOFM'],2);
                $valores[$key] = array('type' => 'time', 'label' => $value['ETIQUETAEFM'], 'Mask' => $mask, 'MaskParam' => $maskParam, 'group' =>$value['GRUPO'],'orden' => $this->ChangeOrderToDecimal($value['ORDEN']), 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'required' => ($value['REQUERIDO'] === "1" ? true : false), 'enabled' => ($value['DISPONIBLE'] === "1" ? true : false), 'name' => $value['CAMPO'], 'subtype' => 'text','ruleIn' => '', 'ruleOut' => '');
            }
            $inputFinal = $valores;
            foreach ($inputFinal as $input) {
                array_push($this->result, $input);
            }
        }
    }

    public function GenerateFecha($input)
    {
        if ($input) {
            foreach ($input as $key => $value) {
                $mask = substr($value['IDELEMENTOFM'],0,2);
                $maskParam = substr($value['IDELEMENTOFM'],2);
                $valores[$key] = array('type' => 'date', 'label' => $value['ETIQUETAEFM'], 'Mask' => $mask,'MaskParam' =>$maskParam , 'group' =>$value['GRUPO'],'orden' => $this->ChangeOrderToDecimal($value['ORDEN']), 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'required' => ($value['REQUERIDO'] === "1" ? true : false), 'enabled' => ($value['DISPONIBLE'] === "1" ? true : false), 'name' => $value['CAMPO'], 'subtype' => 'text','ruleIn' => '', 'ruleOut' => '');
            }
            $inputFinal = $valores;
            foreach ($inputFinal as $input) {
                array_push($this->result, $input);
            }
        }
    }

    public function GenerateTitle($title){
        if ($title) {
            //dd($title);
            $values = null;
            foreach ($title as $key => $value) {
                if($value['IDELEMENTOFM'] == ''){
                    die(json_encode(array('error' => 'Error en la configuracion del LABEL','field' => $value, 'solution' =>'No se ha colocado un id al elemento LABEL')));
                }
                if($value['ETIQUETAEFM'] == ''){
                    die(json_encode(array('error' => 'No es posible crear un elemento LABEL si el texto del título está en blanco','field' => $value, 'solution' =>'Coloque un texto al LABEL.')));
                }
                $values[$key] = array('type' => 'subtitle','subtype'=> 'h3', 'name' => $value['IDELEMENTOFM'], 'label' => $value['ETIQUETAEFM'], 'group' =>$value['GRUPO'], 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'orden' => $this->ChangeOrderToDecimal($value['ORDEN']));
            }
            $inputFinal = $values;
            foreach ($inputFinal as $title) {
                array_push($this->result, $title);
            }
        }
    }

    public function GenerateSection($section)
    {
        $valores = null;
        if ($section) {
            foreach ($section as $key => $value) {
                $actual = $value['IDELEMENTOFM'];
                if($actual == "" || $actual == null)
                {
                    die(json_encode(array('error' => 'Error en la configuracion de una Seccion o FieldSet', 'field' => $value, 'solution' => 'Debe colocar un id al fieldset')));
                }
                if($value['ETIQUETAEFM'] !== null){
                $valores[$key] = array('type' => 'header','subtype'=> 'h2', 'name' => $value['IDELEMENTOFM'], 'label' => $value['ETIQUETAEFM'], 'group' =>$value['GRUPO'], 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'orden' => $this->ChangeOrderToDecimal($value['ORDEN']));
                }
            }

            if($valores)
            {
                $inputFinal = $valores;
                foreach ($inputFinal as $section) {
                    array_push($this->result, $section);
                }

            }
        }
    }

    public function GenerateGroup($group)
    {
        $valores = [];
        $header = [];

        if ($group) {
            foreach ($group as $key => $value) {
                $id = substr($value['IDELEMENTOFM'], 0,5);
                if($id == "" || $id == null)
                {
                    die(json_encode(array('error' => 'Error en la configuracion del Grupo','field' => $value, 'solution' =>'Debe colocar un id para este grupo o fieldset')));
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
            }
            }
            return $valores;
        }

    }

    public function GenerateArea($area)
    {
        if ($area) {
            foreach ($area as $key => $value) {

                $valores[$key] = array('type' => 'textarea', 'label' => $value['ETIQUETAEFM'], 'group' =>$value['GRUPO'],'orden' => $value['ORDEN'], 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'required' => ($value['REQUERIDO'] === "1" ? true : false),'enabled' => ($value['DISPONIBLE'] === "1" ? true : false), 'name' => $value['CAMPO'], 'length' =>null,'ruleIn' => '', 'ruleOut' => '');
            }
            $inputFinal = $valores;
            foreach ($inputFinal as $area) {
                array_push($this->result, $area);
            }
        }
    }

    public function GenerateFile($input)
    {
        if ($input) {
            foreach ($input as $key => $value) {
                //Validate if actual document is enabled for multiple docs
                $actual =  $value['CAMPO'];
                if($actual == '' || $actual == null)
                {
                    die(json_encode(array('error' => 'Error en configuración de documentos', 'field' => $value, 'solution'=>'Debe colocar un identificador al campo de archivo en el formulario.')));
                }
                $keyWord = "";
                $valores[$key] = array('type' => 'file', 'label' => $value['ETIQUETAEFM'], 'group' =>$value['GRUPO'], 'orden' => $this->ChangeOrderToDecimal($value['ORDEN']), 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'required' => ($value['REQUERIDO'] === "1" ? true : false), 'enabled' => ($value['DISPONIBLE'] === "1" ? true : false), 'name' => $value['CAMPO']);
            }
            $inputFinal = $valores;

            foreach ($inputFinal as $file) {
                array_push($this->result, $file);
            }

        }
    }

    public function GenerateGrids($grids)
    {
        if($grids)
        {
            $c = 1;
            foreach($grids as $key => $value)
            {
                //Quitar palabra desde la posicion 0 hasta 4 para que se quede solo con el id del formulario
                $grid_data = explode('00',$value['IDELEMENTOFM']);

                if($grid_data == "" || $grid_data == null  || count($grid_data) < 2)
                {
                    die(json_encode(array('error' => 'Error en la configuración del Grid', 'field' => $value, 'solution' => 'No se ha configurado correctamente el identificador del formulario. Favor colocar en el Identificador lo siguiente: idformulario00idrelacion según la configuración de su formulario.')));
                }
                $grid_id = $grid_data[0];

                $grid_rel = $grid_data[1];
                $fields = $this->executeQuery($grid_id);

                if(count($fields) == 0)
                {
                    die(json_encode(array('error' => 'Formulario no encontrado', 'field' => $value, 'solution' => 'No existe el formulario del grid:'.$grid_id)));
                }

                $fields = json_decode(json_encode($fields), true);
                $fields = $this->GenerateGridData($fields);

                $valores[$key] = array('type' => 'grid', 'label' => $value['ETIQUETAEFM'], 'group' =>$value['GRUPO'], 'orden' => $this->ChangeOrderToDecimal($value['ORDEN']), 'name' => $value['IDELEMENTOFM'],'relationship' =>$grid_rel,'fields' =>$fields);
                $c++;
            }
            $inputFinal = $valores;

            foreach($inputFinal as $grid)
            {
                array_push($this->result, $grid);
            }
        }
        else
        {
            return array();
        }
    }

    public function GenerateCheck($check)
    {
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
                $valores[0] = array('label' => $value['ETIQUETAEFM'],  'value' => $value['CAMPO'], 'rule'=>$rule);

                $checkFinal[$key] = array('type' => 'checkbox-group', 'label' => $value['ETIQUETAEFM'], "mask"=> null, 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'enabled' => ($value['DISPONIBLE'] === "1" ? true : false), 'group' =>$value['GRUPO'],'orden' => $this->ChangeOrderToDecimal($value['ORDEN']), 'name' => $value['CAMPO'], 'values' => $valores);

            }

            foreach ($checkFinal as $check) {
                array_push($this->result, $check);
            }
        }

    }

    public function GenerateLista($lista)
    {
        if ($lista) {

            foreach ($lista as $key => $value) {
                $persistencia_ids = explode('U',$value['CAMPO']);
                if(count($persistencia_ids) < 2 || $persistencia_ids == null)
                {
                    die(json_encode(array('error' => 'Error en la configuración de la lista.', 'field' => $value, 'solution' => 'Debe colocar los campos de persistencia en el id de la lista.')));
                }
                $persist_value = $persistencia_ids[0];
                $persist_label = $persistencia_ids[1];
                $father_id = null;
                if(count($persistencia_ids) == 3){
                    $father_id = $persistencia_ids[2];
                }

                $query = $value['QRY_LISTAVALOR'];

                preg_match_all("/'([^']+)'/", $value['QRY_LISTAVALOR'], $matches);
                $NombrePadre = end($matches[0]);
                $nombre = str_replace("'", "", $NombrePadre);
                // sacar el nombre del select actual
                $NombreActual = reset($matches[0]);
                $actual = str_replace("'", "", $NombreActual);

                if($actual == "provincia" || $actual == "municipio" || $actual == "sector")
                {
                    $query = $value['QRY_LISTAVALOR'];
                    $EDESUR = preg_match('/\EDESUR\b/',$query);
                    $EDEESTE = preg_match('/\EDEESTE\b/',$query);
                    $EDENORTE = preg_match('/\EDENORTE\b/',$query);
                    $CUSTOM= preg_match('/\CUSTOM_SELECT\b/',$query);
                    $ACTUALEDE = '';


                    if($EDESUR)
                        $ACTUALEDE = 'EDESUR';
                    if($EDEESTE)
                        $ACTUALEDE = 'EDEESTE';
                    if($EDENORTE)
                        $ACTUALEDE = 'EDENORTE';
                  if (($EDESUR || $EDEESTE || $EDENORTE || $CUSTOM) && ($actual == 'provincia' || $CUSTOM) )
                  {
                  //dd($query);
                    $options = $this->SelectForEdes($query,$ACTUALEDE,$actual);
                    $listaFinal[$key] = array('type' => 'select', 'father_id' => $father_id, 'entity'=> $actual, 'group' =>$value['GRUPO'], 'label_persist' => $persist_label, 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'required' => ($value['REQUERIDO'] === "1" ? true : false),'enabled' => ($value['DISPONIBLE'] === "1" ? true : false), 'label' => $value['ETIQUETAEFM'], 'orden' => $this->ChangeOrderToDecimal($value['ORDEN']), 'name' => $persist_value, 'values' => $options,'data_portal' =>false,'select_portal_type' =>$actual);
                  }
                  else
                  {
                    $options = null;
                    $listaFinal[$key] = array('type' => 'select', 'father_id' => $father_id,'entity'=> $actual, 'group' =>$value['GRUPO'], 'label_persist' => $persist_label, 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'required' => ($value['REQUERIDO'] === "1" ? true : false),'enabled' => ($value['DISPONIBLE'] === "1" ? true : false), 'label' => $value['ETIQUETAEFM'], 'orden' => $this->ChangeOrderToDecimal($value['ORDEN']),  'name' => $persist_value, 'values' => 'null', 'data_portal' =>true,'select_portal_type' =>$actual);
                  }
                }
                else
                {
                    if( strpos($value['QRY_LISTAVALOR'], 'DTIPOCLASPADRE') === false  ){
                        $options = $this->selectFormLists($value['QRY_LISTAVALOR']);
                        $listaFinal[$key] = array('type' => 'select', 'father_id' => $father_id, 'entity'=> $actual, 'group' =>$value['GRUPO'], 'label_persist' => $persist_label, 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'required' => ($value['REQUERIDO'] === "1" ? true : false),'enabled' => ($value['DISPONIBLE'] === "1" ? true : false), 'label' => $value['ETIQUETAEFM'], 'orden' => $this->ChangeOrderToDecimal($value['ORDEN']),  'name' => $persist_value, 'values' => $options,'data_portal' =>false,'select_portal_type' =>null);
                    }else{
                        if(strpos($value['QRY_LISTAVALOR'], 'pais') === false ){
                                $options = $this->selectFormLists($value['QRY_LISTAVALOR']);
                                $listaFinal[$key] = array('type' => 'select', 'father_id' => $father_id,'entity'=> $actual, 'group' =>$value['GRUPO'], 'label_persist' => $persist_label,'father' => $nombre, 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'required' => ($value['REQUERIDO'] === "1" ? true : false),'enabled' => ($value['DISPONIBLE'] === "1" ? true : false), 'label' => $value['ETIQUETAEFM'], 'orden' => $this->ChangeOrderToDecimal($value['ORDEN']), 'name' => $persist_value, 'values' => $options,'data_portal' =>false,'select_portal_type' =>null);//$listaFinal[$key] = array('type' => 'select', 'required' => ($value['REQUERIDO'] === "1" ? true : false), 'label' => $value['ETIQUETAEFM'],'father' =>$nombre, 'name' => $value['CAMPO'], 'data' => $options);
                        } else {
                            $options = $this->selectFormLists($value['QRY_LISTAVALOR']);
                                $listaFinal[$key] = array('type' => 'select', 'father_id' => $father_id,'entity'=> $actual, 'group' =>$value['GRUPO'],'label_persist' => $persist_label, 'hidden' => ($value['VISIBLE'] === "1" ? true : false), 'required' => ($value['REQUERIDO'] === "1" ? true : false),'enabled' => ($value['DISPONIBLE'] === "1" ? true : false), 'label' => $value['ETIQUETAEFM'], 'orden' => $this->ChangeOrderToDecimal($value['ORDEN']),  'name' => $persist_value, 'values' => $options,'data_portal' =>false,'select_portal_type' => null);
                        }

                    }
                }
            }

            foreach ($listaFinal as $lista) {
                array_push($this->result, $lista);
            }
        }
    }

    public function SelectForCustomPlaces($query,$ede, $type)
    {
        $query = str_replace(",",",RULES,",$query);
        $options = [];
        $select = DB::connection('sqlsrv')->select($query);

        if (!empty($select)) {
            foreach ($select as $option) {

                if(array_key_exists('EIDCLASPADRE', $option)){
                    if($option->RULES == 'xyz'){
                        $rules = $this->rules[0]->rules? $rules = $this->rules[0]->rules : $rules ='';
                        $rule = $this->AllRules($rules, $option->RULES );

                    }else{
                        $rule="";
                    }
                    array_push($options, array('label' => $option->CTITULOCLAS, 'rule' =>$rule, 'value' => $option->BIDCLASIF,'father' =>$option->EIDCLASPADRE));
                }else{
                    if($option->RULES == 'xyz'){
                        $rules = $this->rules? $rules = $this->rules[0]->rules : $rules ='';
                        $rule = $this->AllRules($rules, $option->RULES );

                    }else{
                        $rule="";
                    }
                    array_push($options, array('label' => $option->CTITULOCLAS,'rule' =>$rule, 'value' => $option->BIDCLASIF));
                }
            }
        }

        return $options;

    }
    public function selectFormLists($query)
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

    function searchOrderBy(string $query)
    {
        $query = strtoupper($query);
        $order_by = "";
        if(preg_match('/\ORDER BY\b/',$query))
        {
            $query_divided = explode('ORDER BY', $query);
            $order_by = "ORDER BY " .$query_divided[1];
        }
        else
        {
            $order_by = "ORDER BY CTITULOCLAS ASC";
        }

        return $order_by;
    }

    //Quita el segundo punto del order  de un elemento del formulario
    public function ChangeOrderToDecimal($value)
    {
        return $value;
    }

    public function ordernaOther($array) {
        return function ($a, $b) use ($array) {
            return strnatcmp($a[$array], $b[$array]);
        };
    }

    public function ordena($array) {
        return function ($a, $b) use ($array) {
            return strnatcmp($a[$array], $b[$array]);
        };
    }

    public function GetInputLength($input)
    {
        $oid = $input['OIDREVISIONFORM'];
        $label = $input['ETIQUETAEFM'];
        $query = "select EMATTRMODEL.FGTYPEATTRIBUTE from efstructform JOIN EMATTRMODEL ON (efstructform.OIDATTRIBUTEMODEL = EMATTRMODEL.OID ) where efstructform.oidrevisionform = '$oid' AND efstructform.FGTYPE = 3 AND efstructform.NMLABEL = '$label'";

        $type = $this->select($query);

        if(count($type) > 0)
        {
            $inputType = ($type[0]->FGTYPEATTRIBUTE == 1 ? 255 : 50);
            return $inputType;
        }
        return null;

    }

    public function SelectForEdes($query,$ede, $type)
    {
        $query = str_replace("--CUSTOM_SELECT","",$query);
        $query = str_replace(",",",EIDCLASPADRE,",$query);
        if($type == 'municipio' && $ede == 'EDESUR')
        {
            $query = "SELECT BIDCLASIF,RULES, CTITULOCLAS, EIDCLASPADRE from dynRDsplClasGen WHERE ATIPOCLAS ='municipio' and DTIPOCLASPADRE ='provincia' and SUBSTRING(BIDCLASIF,1,4) IN (SELECT BIDCLASIF FROM dynrdsplclasgen where atipoclas = 'provincia' and texto501 = 'EDESUR' and EIDCLASPADRE='DO')";
        }
        if($type == 'sector' && $ede == 'EDESUR')
        {
            $queryProvincias = "SELECT BIDCLASIF from dynRDsplClasGen WHERE ATIPOCLAS ='municipio' and DTIPOCLASPADRE ='provincia' and SUBSTRING(BIDCLASIF,1,4) IN (SELECT BIDCLASIF FROM dynrdsplclasgen where atipoclas = 'provincia' and texto501 = 'EDESUR' and EIDCLASPADRE='DO')";
            $query = "SELECT BIDCLASIF,RULES, CTITULOCLAS, EIDCLASPADRE from dynRDsplClasGen WHERE ATIPOCLAS ='sector' and DTIPOCLASPADRE ='municipio' and SUBSTRING(BIDCLASIF,1,6) IN ($queryProvincias)";
        }

        $options = [];
        $select = $this->select($query);

        if (!empty($select)) {
            foreach ($select as $option) {

                if(array_key_exists('EIDCLASPADRE', $option)){

                    array_push($options, array('label' => $option->CTITULOCLAS, 'rule' =>"", 'value' => $option->BIDCLASIF, 'father' =>$option->EIDCLASPADRE));
                }else{
                    array_push($options, array('label' => $option->CTITULOCLAS,'rule' =>"", 'value' => $option->BIDCLASIF));
                }

            }
        }
        return $options;
    }
}
