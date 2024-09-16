<?php
namespace App\Services;

use App\Models\Apps;
use App\Models\Site;
use App\Models\SitePages;
use App\Models\Tabs;

class AppSyncService
{
    public function syncSelectedSitePagesToApp(Site $site, Apps $app, array $selectedPages)
    {

        if ($app == null)
        {
            $app = new Apps();
            $team = $site->teamFromSite($site->id);
            $app->team_id = $team->id;
            $app->appicon = $site->logo_image;
            $app->app_name = $site->site_name;
            $app->page_title = $site->site_name;
            $app->page_url = $site->site_name;
            $app->sort_order = '1';

            $newApp = $app->toArray();
            $app::create($newApp);
        }
        // Add each selected site page as a tab to the app
        $index = 0;
        foreach ($selectedPages as $page) {
            $sitePage = collect($site->sitePages)->firstWhere('id', $page);
            if (!$sitePage) {
                throw new \Exception("Site page not found: $page");
            }
            $tab = Tabs::make();
            $tab->page_title = $sitePage->title;
            $tab->page_url = '/page/'.$sitePage->section; 
            $tab->app_id = $app->id;  
            $tab->sort_order = $index++; 
            $tab->parent = 0; 
            $tab->icon = 'beach_access';
            $tab->request_header = '{"Authorization":"Bearer [USER_TOKEN]"}';
            $tab->restrict_role = $sitePage->user_level;
                    
            $tab->save();
        }

    }
}
?>