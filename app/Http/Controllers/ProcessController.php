<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class ProcessController extends Controller
{
    //




    public function ValidateProcess($id)
    {
        $query = "select processid from dynproform where processid ='$id'";
        $result =  DB::select($query);
        if($result){
            return array('message'=>'El id de proceso existe','status'=>'200', 'process_id'=>$id);
        }else{
            return array('message'=>'El id de proceso no existe','status'=>'404', 'process_id'=>$id);
        }
        
    }
}
