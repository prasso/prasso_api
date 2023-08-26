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

    public function getTemplateData($site_page, $placeholder, $user=null){
       
        $jsonData = $this->getTemplateDataJSON($site_page, $user);

        $site_page_description = str_replace($placeholder, $jsonData, $site_page->description);

        return $site_page_description;
       
    }
/**
 * troubleshooting data missing: check that the where clause in the site page is set to the id of the site
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

            $sql = $template_data->template_data_query. ' as display';

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
                    info('subteams: ' . json_encode($subteamIds));
                }
                if ($subteamIds != [])
                {$query = $query->whereIn('fk_team_id', $subteamIds);}
            }
            
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
        }
        else{
            $jsonData = $template_data->default_blank;
        }
        if ($template_data->include_csrf){
            $phpObject = json_decode($jsonData);
            $phpObject->csrftoken = csrf_token();
            $jsonData = json_encode($phpObject);
        }
        return $jsonData;
    }

}





