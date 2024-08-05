<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\SuperAdmin;
use Auth;


class AuthenticateSuperAdmin
{
    
    public function __construct(SuperAdmin $suser)
    {
    }

   public function handle($request, Closure $next)
   {
      $user = \Auth::user();
      if (!isset($user)){
         session()->flash('message', config('constants.UNAUTHORIZED'));
         return redirect('/login');
       }
      $superAdminModel = new \App\Models\SuperAdmin();
      $adminuser = $superAdminModel->fetchUserByCredentials($user->email);

      if ($adminuser == null) {
        return redirect('/login')->with('message', config('constants.UNAUTHORIZED'));
      }
      return $next($request);
   }
}
