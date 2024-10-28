<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\SitePages;
use App\Models\SitePageTemplate;
use App\Models\SitePageData;


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

    public function getTemplateData($site_page, $placeholder, $user=null){
       
        $jsonData = $this->getTemplateDataJSON($site_page, $user);

        $site_page_description = str_replace($placeholder, $jsonData, $site_page->description);

        //$this->dd_with_callstack($site_page_description);
        return $site_page_description;
       
    }
    
    /**
     * when troubleshooting data missing: check that the where clause in the site page is set to the id of the site
     */
    public function getTemplateDataJSON($site_page, $user=null){
       
        $template_data = SitePageTemplate::where('templatename', $site_page->template)->first();
        if ($template_data == null)
        {
            return '';
        }
        $modelClassName = $template_data->template_data_model;
        $model = resolve($modelClassName);
      
        if ($site_page->where_value != null && $site_page->where_value != '' && $site_page->where_value != -1)
        { 

            $sql = $template_data->template_data_query. ' as display, id ';

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
            
            if ($user != null) {
                $subteamIds = [];
                if ($user != null){
                    $subteamIds = $user->team_member->pluck('team_id')->toArray();
                }
                if ($subteamIds != [])
                {$query = $query->whereIn('fk_team_id', $subteamIds);}
            }
            
            $data = $query   
                ->selectRaw($sql)
                ->get();
             //process consolidation to share code that ensures consistent format
             if ($data->isEmpty()) {
                $json_data = $template_data->default_blank ?? "";
            } else {
                $json_data = $data->toJson();
            }
           
            $site_page_data = SitePageData::factory()->make([
                'fk_site_page_id' => $site_page->id,
                'data_key' => uniqid(),
                'json_data' => $json_data,
            ]);

            $jsonData = $this->processJSONData($site_page_data, $template_data);
           // Controller::dd_with_callstack($jsonData);
        
        }
        else{
            $jsonencodedData = $template_data->default_blank === null ? [] : json_decode($template_data->default_blank);

            if ($template_data->include_csrf){
                $jsonencodedData->csrftoken = csrf_token();
            }
            $jsonData = json_encode($jsonencodedData);
        }
        return $jsonData;
    }
   
    // make sure the json matches default_blank
    public function processJSONData($site_page_data, $template_data){
        // make sure the json matches default_blank
        $template_data_default_blank = json_decode($template_data->default_blank, true);
        if ($template_data_default_blank === null ) {
            return $site_page_data->json_data;
        }
        
       $jsonData = json_decode($site_page_data->json_data, true);

        $missingKeys = array_diff_key($template_data_default_blank, $jsonData);
        $jsonData = array_merge($jsonData, $missingKeys);
        $jsonData['data_key'] = $site_page_data->data_key;

        if ($template_data->include_csrf){
            $jsonData['csrftoken'] = csrf_token();
        }
        $jsonDataEncoded = json_encode($jsonData);
        return $jsonDataEncoded;
    }
}





