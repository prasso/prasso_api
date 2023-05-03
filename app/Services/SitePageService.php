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
            'template' => $request['template'],
        ]);
        
        $message = $updatedSitePage ? 'Site Page Updated Successfully.' : 'Site Page Created Successfully.';

        return json_encode($message);
    }

    /** 
     * // the template data query is stored in the template record. get the sql from the template record
        // format of this template data query is tablename:rawSql
        //'App\Models\SiteMedia':'CONCAT(\'{"s3media_url":"\', s3media_url, \'","media_title":"\', media_title, \'","thumb_url":"\', thumb_url,\'"}\') as thumb_display')
        // code
     */
    public function getTemplateData($site_page){
        
        $template_data_query = SitePageTemplate::where('templatename', $site_page->template)->first()->template_data_query;
        
        $parts = explode(':', $template_data_query, 2);
        $modelClassName = trim($parts[0], "'");
        $sql = trim($parts[1], "'"). ' as display';

        $model = resolve($modelClassName);

        $siteMedia = $model->where('fk_site_id', $site_page->fk_site_id)
            ->orderBy('media_date', 'desc')
            ->selectRaw($sql)
            ->get();
        
                
        $stringArray = $siteMedia->map(function ($item) {
            return $item->display;
        })->toArray();

        $jsonString = implode(',', $stringArray);

        $placeholder = '[DATA]';
        $site_page_description = str_replace($placeholder, $jsonString, $site_page->description);

        return $site_page_description;
       
    }
}





