<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Instructor;
use Auth;


class AuthenticateInstructor
{
    private $instruc;

    public function __construct(Instructor $suser)
    {
      $this->instruc = $suser;
    }

   public function handle($request, Closure $next)
   {
      $guard = Auth::guard('instructor');

      if ($guard->check()) 
      {
        $user = \Auth::user();
        Log::info('in AuthenticateInstructor, handle, after guard->check: ' . json_encode($user));

        //double check because this guard/provider isn't plugged in properly yet
        $instructoruser = $this->instruc->fetchUserByCredentials($user->email);
        
        if ($instructoruser == null)
        {
          session()->flash('message', config('constants.UNAUTHORIZED'));
          return redirect('/login');
        }
        $credentials['email'] = $user->email;
        $credentials['password'] = $user->password;

        $guard->validate($credentials);

      }
      return $next($request);
   }
}
