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
      //Log::info('in __construct.AuthenticateSuperAdmin, : ' . json_encode($suser));

    }

   public function handle($request, Closure $next)
   {
      $guard = Auth::guard('superadmin');

      if (!$guard->check()) 
      {
        $user = \Auth::user();
   // Log::info('in AuthenticateSuperAdmin, handle, after guard->check: ' . json_encode($user));

        //double check because this guard/provider isn't plugged in properly yet
        if ($user != null)
        {
          $adminuser = $this->superuser->fetchUserByCredentials($user->email);
          
          if ($adminuser == null)
          {
            session()->flash('message', config('constants.UNAUTHORIZED'));
            return redirect('/login');
          }
          $credentials['email'] = $user->email;
          $credentials['password'] = $user->password;

          $guard->validate($credentials);
        }
      }
      return $next($request);
   }
}
