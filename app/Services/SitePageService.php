<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\SitePages;


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

    public function getTemplate($site_page){
        return $site_page->description;
        /**
         * description contains all the html if this site_page specifies a master_page.
         * 
         * here's the problem for grapes editor: we edit the sitepage->description but
         * we need the js and css specified in the sitepage->masterpage to lay it out properly
         */
        //open the template file named by the template
        //$content = file_get_contents(resource_path() . '/templates/'.$template.'.txt');
       // lklk
    }
}





