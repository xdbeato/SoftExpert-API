<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\DataQuery;
use App\Traits\Workflow;

class SoftexpertController extends Controller
{
    use DataQuery;
    use Workflow;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(string $entity)
    {
        $query = "SELECT * FROM dyn$entity";

        try {
            $result = $this->select($query);
        }catch (\Exception $e){
            return response()->json(['status' =>'failure', 'message' => $e->getMessage(), 'data' =>null]);
        }

        $status = (count($result) > 0 ? 'success': 'failure');
        return response()->json(['status' => $status, 'message' =>'query execute successfully', 'data' =>$result]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $workflow = $this->newWorkFlow($request->entity);

            if($workflow['Status'] === 'SUCCESS')
            {
                $new_field = ['key' => 'recordid', 'value' => $workflow['RecordID']];
                $fields = $request->fields;
                array_push($fields,$new_field);
                $edit_data = $this->editEntityRecord($workflow['RecordID'],$request->entity,$fields,false);

            }

        }catch (\Exception $e)
        {
            return response()->json(['status' =>'error', 'message' => $e->getMessage()]);
        }

        if ($edit_data['Status'] == 'SUCCESS')
        {
            return response()->json(['status' =>'success','message'=> 'record created successfully']);
        }

        return response()->json(['status' =>'failure', 'message' => $edit_data['Detail']]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($entity,$id)
    {
        $query = "SELECT * FROM dyn$entity WHERE recordid = '$id'";

        try {
            $result = $this->select($query);
        }catch (\Exception $e){
            return response()->json(['status' =>'failure', 'message' => $e->getMessage(), 'data' =>null]);
        }

        $status = (count($result) > 0 ? 'success': 'failure');
        return response()->json(['status' => $status, 'message' =>'query execute successfully', 'data' =>$result]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($entity,$id)
    {
        try {
            $this->deleteWorkflowAndData($id);
        }catch (\Exception $e)
        {
            return response()->json(['status' =>'failure', 'message' => $e->getMessage(), 'data' =>null]);
        }

        try {
            $query = "DELETE FROM dyn$entity WHERE recordid = '$id'";
            $this->delete($query);
        }catch (\Exception $e)
        {
            return response()->json(['status'=> 'failure', 'message' => $e->getMessage(), 'data' =>null]);
        }

        return response()->json(['status'=>'success', 'message' =>'record deleted correctly', 'data'=>null]);

    }
}
