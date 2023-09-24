<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;


class SiteRequest extends BaseRequest {

    protected $id;

    public function __construct($id = null)
    {
        parent::__construct();
        $this->id = $id;
    }
    
    public function rules() {
        return array_merge(parent::rules(), [
            'site_name' => 'required',
            'description' => ['required','max:150'],
            'host' => [
                'required',
                Rule::unique('sites', 'host')->ignore($this->id),
            ],
            'main_color' => 'required',
            'logo_image' => 'required_without:photo',
            'database' => 'required',
            'favicon' => 'required',
            'photo' => 'required_without:logo_image|max:1024',
            'supports_registration' => 'required',
            'subteams_enabled' => 'required',
            'app_specific_js' => 'nullable',
            'app_specific_css' => 'nullable',
            'does_livestreaming' => 'required',
            'image_folder' => 'required|ends_with:/',
        ]);
    }
}

