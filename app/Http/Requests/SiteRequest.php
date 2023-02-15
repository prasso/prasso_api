<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Log;

class SiteRequest extends BaseRequest {

    public function rules() {
        return array_merge(parent::rules(), [
                'site_name' => 'required',
                'host' => 'required',
                'main_color' => 'required',
                'logo_image' => 'required',
                'database' => 'required',
                'favicon' => 'required'
            
            ]);
    }
}