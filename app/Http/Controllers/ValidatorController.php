<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\FormularyOperations;
use App\Traits\Workflow;

class ValidatorController extends Controller
{
    use FormularyOperations;
    use Workflow;

    public function __construct()
    {
        set_time_limit(8000000);
    }
    
    public function ValidateForm(Request $request)
    {
        $_request = new Request();
        $formulary = $request->fields;
        $fields = $this->BuildFormForTest($formulary[0]);
        $workflow = $this->newWorkFlow($request->process_id);
       // dd($workflow);
        $result = [];
        if($workflow['Code'] == 1)
        {
            $workflowid = $workflow['RecordID'];
            foreach($fields as $field)
            {
                $current = $this->editEntityRecord($workflowid,$request->entity,[$field],true);
                $current['Campo'] = ['id' => $field['key'], 'Nombre' => $field['label'],'tipo' => $field['type'], 'sentValue' => $field['value']];
                
                array_push($result, $current);
                if($current['Code'] == '-51')
                {
                    return response()->json(['Status' => 'failure', 'message' =>'Esta tabla se encuentra en modificacion debido a que se ha agregado el campo: '.$field['key'].'y los cambios no se han escrito en la base de datos.', 'solution' =>'Liberar la tabla: '.$request->entity],500);
                }
                
            }         
        }
        else
        {
            return response()->json([$workflow],403);  
        }

        return response()->json([$result],200);        

    }
}
