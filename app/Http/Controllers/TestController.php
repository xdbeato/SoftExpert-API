<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function __construct()
    {
        set_time_limit(8000000);
    }
    public function test()
    {

        $sleepTime = 220;
        sleep ( $sleepTime );

        return response()->json(['status' =>'finalized', 'time' =>$sleepTime]);


    }
}
