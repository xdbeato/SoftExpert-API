<?php

namespace App;

class DocumentResponse {

    public $success;
    public $iddocument;
    public $message;

    public function __construct($success,$iddocument,$message){
        $this->success      = $success;
        $this->iddocument   = $iddocument;
        $this->message      = $message;
    }

    
    
}