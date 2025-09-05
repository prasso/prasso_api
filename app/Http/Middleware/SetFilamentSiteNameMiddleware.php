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
        return $next($request);
        // Get the host from the request
        $host = $request->getHost();
        $hostWithPort = $request->getHttpHost(); // Includes port if present
        
        // Try to find the site based on the host
        $site = Site::getClient($host);
        
        // If no site found with just the host, try with the port
        if (!$site && $host !== $hostWithPort) {
            $site = Site::getClient($hostWithPort);
        }
        
        // For local development, if still no site found and host is localhost, try a fallback
        if (!$site && ($host === 'localhost' || strpos($hostWithPort, 'localhost:') === 0)) {
            // Try to find a site with 'localhost' in its host field
            $site = Site::where('host', 'like', '%localhost%')->first();
            
            // If still no site found, create a temporary site object for development
            if (!$site) {
                $site = new Site();
                $site->id = 0;
                $site->site_name = 'AutoProHub';
            }
        }
        
        // Set the site name for Filament if site was found
        if ($site) {
            Config::set('app.name', $site->site_name);
        }
        
        return $next($request);
    }
}
