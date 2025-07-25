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
use Faxt\Invenbin\Support\Facades\InvenbinPanel;
use Prasso\Messaging\Support\Facades\MessagingPanel;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
    


class AppServiceProvider extends ServiceProvider
{
    public $site; 
    public static $allowedUris = ['/user/profile','/login', '/dashboard', '/register', '/forgot-password', '/privacy', '/terms', '/teams','privacy','terms'];
     

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
      MessagingPanel::register();
      InvenbinPanel::register();

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Request $request, Site $site)
    {
      
        // Trust all proxies or specify your proxy IPs
        Request::setTrustedProxies(
          [request()->getClientIp()],
          SymfonyRequest::HEADER_X_FORWARDED_FOR |
          SymfonyRequest::HEADER_X_FORWARDED_HOST |
          SymfonyRequest::HEADER_X_FORWARDED_PORT |
          SymfonyRequest::HEADER_X_FORWARDED_PROTO |
          SymfonyRequest::HEADER_X_FORWARDED_AWS_ELB
      );

      // Force HTTPS if your app should always use HTTPS
      if ($this->app->environment('production') || $this->app->environment('staging')) {
          URL::forceScheme('https');
          $this->app['request']->server->set('HTTPS', 'on');
      }

      Schema::defaultStringLength(191);

      // Get the current request's URI
       $uri = $request->getRequestUri();

       // Run the logic only if the URI matches a certain pattern
        // Check if the URI matches any of the allowed URIs using a partial match with array_filter and count functions
        $matches = array_filter(AppServiceProvider::$allowedUris, function ($allowedUri) use ($uri) {
          return strpos($uri, $allowedUri) !== false;
      });

      if (count($matches) > 0) {
        AppServiceProvider::loadDefaultsForPagesNotUsingControllerClass($site);
       }
 
    }

      // this is called repeatedly when debugging
    public static function loadDefaultsForPagesNotUsingControllerClass($site)
    {
      //info('boot loadDefaultsForPagesNotUsingControllerClass');
      $site = $site;
      if ($site->host == '')
      {
        $site = Controller::getClientFromHost();        
      }

      // Skip setting masterpage for hosted sites
      if (!($site != null && !empty($site->deployment_path) && !empty($site->github_repository))) {
          $masterpage = Controller::getMasterForSite($site);
          View::share('masterPage', $masterpage);
      }

      View::share('site', $site);
  }
}
