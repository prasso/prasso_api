<?php

namespace App\Http\Middleware;

use App\Models\Site;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Filament\Facades\Filament;

class SetFilamentSiteNameMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Get the host from the request
        $host = $request->getHttpHost(); 

        // Try to find the site based on the host
        $site = Site::getClient($host);
        
        // Set the site name for Filament if site was found
        if ($site) {
            Config::set('app.name', $site->site_name);
        }
        
        return $next($request);
    }
}
