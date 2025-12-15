<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HandlePwaSession
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Add headers to prevent caching of authenticated pages
        if (Auth::check()) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }
}
