<?php

namespace App\Traits;
use Illuminate\Support\Str;
use Exception;

trait DataTest {

    public function getChildsFake(){
       return "[{\"OID\":\"d616c0a6384f1e07be5a81b878db118d\",\"NRVERSION\":\"0\",\"BNCREATED\":\"-735378928\",\"FGENABLED\":\"1\",\"OIDREVISIONFORM\":\"8a7f858c6fa117a6016fc4a58d8b4477\",\"FGSYSTEM\":\"0\",\"BNUPDATED\":\"1579812586000\",\"NMUSERUPDATE\":\"Yeny Torres\",\"TEXTO25501\":null,\"TEXTO5001\":\"19\\u00ba 15' 30.83\\\" N\",\"TEXTO5002\":\"71\\u00ba 41' 48.41\\\" W\",\"TEXTO5003\":\"615 Mts\",\"TEXTO5004\":\"60.97 Mts\",\"TEXTO5005\":null,\"TEXTO5006\":\"DO32\",\"TEXTO5007\":\"Santo Domingo\",\"TEXTO5008\":\"DO3207\",\"TEXTO5009\":\"Pedro Brand\",\"TEXTO5010\":null,\"TEXTO5011\":null,\"TEXTO5012\":null,\"TEXTO5013\":null,\"OIDABCG8MTKU8JCDGW\":\"be26a0710c70e9e5911cceabf780fde6\"},{\"OID\":\"fc19fe54257756a61be72fb5fc67e640\",\"NRVERSION\":\"0\",\"BNCREATED\":\"-386382928\",\"FGENABLED\":\"1\",\"OIDREVISIONFORM\":\"8a7f858c6fa117a6016fc4a58d8b4477\",\"FGSYSTEM\":\"0\",\"BNUPDATED\":\"1580161582000\",\"NMUSERUPDATE\":\"Yeny Torres\",\"TEXTO25501\":\"Entrada Carretera Principal \",\"TEXTO5001\":\"19\\u00ba 15' 30.83\\\" N\",\"TEXTO5002\":\"71\\u00ba 41' 48.41\\\" W\",\"TEXTO5003\":\"615 Mts\",\"TEXTO5004\":\"60.97 Mts\",\"TEXTO5005\":null,\"TEXTO5006\":\"DO18\",\"TEXTO5007\":\"Puerto Plata\",\"TEXTO5008\":\"DO1807\",\"TEXTO5009\":\"Sos\\u00faa\",\"TEXTO5010\":\"DO1807020100101\",\"TEXTO5011\":\"Cabarete\",\"TEXTO5012\":\"03\",\"TEXTO5013\":\"Instalaci\\u00f3n de Tanque de Agua\",\"OIDABCG8MTKU8JCDGW\":\"be26a0710c70e9e5911cceabf780fde6\"}]";
    }
}