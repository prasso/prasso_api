<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use App\Models\Site;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;


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

      $site = $site;
      if ($site->host == '')
      {
        $host = $request->getHost();
        $site = Site::getClient($host);         
      }


//info('site: ' . json_encode($site));

      View::share('site', $site);

    
    }

 
}
