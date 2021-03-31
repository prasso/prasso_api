<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Site;

class SiteRecognition
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
        $host = $request->getHost();

        $session_host = \App::make(Site::class, ['host' => $host ]);  
        if ($session_host->host == null)
        {
            $session_host = Site::getClient($host);
        }
        
        app()->instance(Site::class, $session_host);
        return $next($request);
    }
}
