<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class SolicitudController extends Controller
{
    public function OpenRequestInabimaSE(Request $request)
    {
        $request->ciudadano_id = str_replace('-','',$request->ciudadano_id);
        $servicio = $request->servicio;
        switch ($servicio) {
            case 'INABIMA01':
                $query = "SELECT count(texto25510) 'Solicitudes' FROM dynINABIMA01SolSer WHERE  texto5015='".$request->ciudadano_id."' and texto25510 in  ('Abierto','Finalizado')";
                break;
            case 'INABIMA02-':
                $query = "";
                break;
            case 'INABIMA03-':
                $query = "";
                break;
            case 'INABIMA04-':
                $query = "";
                break;
            case 'INABIMA05':
                $query = "SELECT count(texto25530) 'Solicitudes' FROM dyninabima05SolSer WHERE  texto5006='".$request->ciudadano_id."' and texto25530 in  ('Abierto','Finalizado')";
                break;
            case 'INABIMA06':
                $query = "SELECT count(texto25510) 'Solicitudes' FROM dyninabima06solser WHERE  texto5006='".$request->ciudadano_id."' and texto25510 in ('Abierto','Finalizado')";
            break;
            default:
                return response()->json(['status' => 'failure', 'message' => 'Este servicio no posee consulta de Solicitudes Abiertas'],404);
                break;
        }

    
        $result =  DB::connection('sqlsrv')->select($query); 
        if(count($result) > 0)
        {
            $solicitudes = $result[0]->Solicitudes;
            if($solicitudes > 0)
            {
                return response()->json(['status'=> 'success', 'HasOpenRequest' => true ],200);  
            }
            else
            {
                return response()->json(['status'=> 'success', 'HasOpenRequest' => false ],200);  
            }
            
        }

        return response()->json(['status'=> 'failure', 'message' => 'No se han encontrado registros de este ciudadano' ],200);


    }
}
