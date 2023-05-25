<?php

namespace App\Http\Requests;

class SitePageTemplateRequest extends BaseRequest
{
    protected $id;

    public function __construct($id = null)
    {
        parent::__construct();
        $this->id = $id;
        
    }
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            'template.templatename' => 'required',//starts with sitepage.templates. and is unique
            'template.title' => 'required',
            'template.description' => 'required',
            'template.template_data_model' => 'required', //must be a model in the app/Models directory
            'template.template_where_clause' => 'required',// either fk_site_id or id (must be a field in source template_data_model/ table)
            'template.template_data_query' => 'required',//a single sql entity -either a field name or sql such as CONCAT('{"s3media_url":"', s3media_url, '","media_title":"', media_title, '","thumb_url":"', thumb_url,'"}') 
            'template.order_by_clause' => 'required',
            'template.default_blank' => 'nullable',
            'template.include_csrf' => 'nullable',
            
        ]);
        
    }
    public function validated()
    {
        return $this->validated()['template'];
    }
}
