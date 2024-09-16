<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Support\Facades\Log;
class BlockSwaggerAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check if the environment is production
        if (app()->environment('production')) {
            //if authenticated user is superadmin, allow it
            $user = \Auth::user();
            if (!$user->isSuperAdmin()){
            Log::info('should be blocked');
            // Block access to the Swagger documentation route
            return response()->json(['message' => 'not today'], 403);
        }
        }

        // Allow access to other routes
        return $next($request);
    }
}
