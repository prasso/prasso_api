<?php

namespace App\Http\Requests;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

abstract class BaseRequest extends Request {

    public function rules() {
        return [ /* common rules */ ]; 
    }
}

