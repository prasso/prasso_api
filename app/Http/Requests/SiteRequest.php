<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Log;

class SiteRequest extends BaseRequest {

    public function rules() {
        return array_merge(parent::rules(), [
                'site_name' => 'required',
                'description' => 'required',
                'host' => 'required',
                'main_color' => 'required',
                'logo_image' => 'required_without:photo|starts_with:http',
                'database' => 'required',
                'favicon' => 'required',
                'photo' => 'required_without:logo_image|max:1024'
            
            ]);
    }
}