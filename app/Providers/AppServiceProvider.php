<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use App\Models\Site;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\Controller;
use App\Models\SitePages;
use App\Models\MasterPage;


class AppServiceProvider extends ServiceProvider
{
    public $site; 

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Request $request, Site $site)
    {
      Schema::defaultStringLength(191);
      $this->loadDefaultsForPagesNotUsingControllerClass($site);
 
    }

      // this is called repeatedly when debugging
    public function loadDefaultsForPagesNotUsingControllerClass($site)
    {
      //info('boot loadDefaultsForPagesNotUsingControllerClass');
      $site = $site;
      if ($site->host == '')
      {
        $site = Controller::getClientFromHost();        
      }

      $masterpage = Controller::getMasterForSite($site);

      View::share('site', $site);
      View::share('masterPage', $masterpage);
  }
}
