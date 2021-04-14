<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\SuperAdmin;
use Auth;


class AuthenticateSuperAdmin
{
    private $superuser;

    public function __construct(SuperAdmin $suser)
    {
      $this->superuser = $suser;
    }

   public function handle($request, Closure $next)
   {
       $guard = Auth::guard('super_admin');

       // If request does not comes from logged in admin
       // check one more time cause this middleware/guard/provider aint workin right yet
       // then he shall be redirected to admin Login page
       if (! $guard->check()) 
       {
            $user = \Auth::user();

       Log::info($user);
      
            //double check because this guard/provider isn't plugged in properly yet
            $adminuser = $this->superuser->fetchUserByCredentials($user->email);
            
            if ($adminuser == null)
            {
                Log::info('Sending to Login');
                return redirect('/login');
            }
            $credentials['email'] = $user->email;
            $credentials['password'] = $user->password;

            $guard->validate($credentials);

       }

       return $next($request);
   }
}
