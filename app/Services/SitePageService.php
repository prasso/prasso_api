<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\SitePages;
use App\Models\SitePageTemplate;


class SitePageService 
{
    public function saveSitePage($request)
    {
        $updatedSitePage = SitePages::updateOrCreate(['id' => $request['id']], 
            ['fk_site_id' => $request['fk_site_id'],
            'section' => $request['section'],
            'title' => $request['title'],
            'description' => $request['description'],
            'url' => $request['url'],
            'headers' => $request['headers'],
            'masterpage' => $request['masterpage'],
            'login_required' => $request['login_required'],
            'user_level' => $request['user_level'],
            'template' => $request['template'],
            'style' => $request['style'],
            'where_value' => $request['where_value']
        ]);
        
        $message = $updatedSitePage ? 'Site Page Updated Successfully.' : 'Site Page Created Successfully.';

        return json_encode($message);
    }

    public function getTemplateData($site_page, $placeholder){
       
        $jsonData = $this->getTemplateDataJSON($site_page);

        $site_page_description = str_replace($placeholder, $jsonData, $site_page->description);

        return $site_page_description;
       
    }

    public function getTemplateDataJSON($site_page){
       
        $template_data = SitePageTemplate::where('templatename', $site_page->template)->first();
  
        $modelClassName = $template_data->template_data_model;
        $model = resolve($modelClassName);

        $sql = $template_data->template_data_query. ' as display';
        
 //info(json_encode($site_page));
        $where_clause_field = str_replace('???', $site_page->where_value, $template_data->template_where_clause);
        $fieldValue = $site_page->getAttribute($where_clause_field);
        if ($fieldValue == null) //the where clause is not a field in the site page table
        {
            $query = $model->whereRaw($where_clause_field);
        }
        else
        { 
            $query = $model->where($where_clause_field, $fieldValue);
        }

        $order_by_clause = $template_data->order_by_clause;
        if ($order_by_clause != NULL)
        {
            $parts = explode(':', $order_by_clause, 2);
            $fieldInOrder = trim($parts[0], "'");
            if (count($parts) > 1) {
                $ascDesc = trim($parts[1] ?? '', "'");
                if (!$ascDesc) {
                    $ascDesc = 'desc';
                }
            } else {
                $ascDesc = 'desc';
            }
            if ($ascDesc == NULL) {
                $ascDesc = 'desc';
            }
            $query = $query->orderBy($fieldInOrder, $ascDesc);
        }
        info($query->toSql());

        $data = $query   
            ->selectRaw($sql)
            ->get();
        if ($data->isEmpty())
        {
            $jsonData = $template_data->default_blank;
        }
        else
        {
            $jsonData = $data->toJson();
        }
        info($jsonData);

        if ($template_data->include_csrf){
            $phpObject = json_decode($jsonData);
            $phpObject->csrftoken = csrf_token();
            $jsonData = json_encode($phpObject);
        }
        return $jsonData;
    }

}





