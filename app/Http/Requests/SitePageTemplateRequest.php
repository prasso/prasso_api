<?php

namespace App\Http\Requests;
use Illuminate\Validation\Rule;

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
        
        $rules = [
            'template.templatename' => [
                'required',
                Rule::unique('site_page_templates', 'templatename')->ignore($this->id),
                'starts_with:sitepage.datatemplates.',
            ],'template.title' => 'required',
            'template.description' => 'required',
            'template.template_data_model' => 'required', // must be a model in the app/Models directory
            'template.template_data_query' => 'required', // a single SQL entity - either a field name or SQL such as CONCAT('{"s3media_url":"', s3media_url, '","media_title":"', media_title, '","thumb_url":"', thumb_url,'"}')
            'template.order_by_clause' => 'nullable',
            'template.default_blank' => 'nullable',
            'template.include_csrf' => 'nullable',
            'template.template_where_clause' => 'required_without:template.include_csrf'
        ];
       

    
        return array_merge(parent::rules(), $rules);
        
        
    }
    public function validated()
    {
        return $this->validated()['template'];
    }
}
