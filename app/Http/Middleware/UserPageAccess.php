<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Auth;

class UserPageAccess
{
    

   public function handle($request, Closure $next)
   {
      $user = \Auth::user();
      if ($user == null)
      {
        if (!UserPageAccess::authorizeUser($request)){
          return redirect('/login');
        }
        
      }
    
      return $next($request);
   }

   public static function authorizeUser($request){
    $accessToken  = $request->header(config('constants.AUTHORIZATION_'));
        $accessToken = str_replace("Bearer ", "", $accessToken);
        //if no accesstoken, check if we have an X-Authorization header present
        if($accessToken == '' && $auth = $request->header(config('constants.XAUTHORIZATION_'))) {
          info('setting authorization header from xauthorization header');
          $request->headers->set('Authorization', $auth);
        }
        if (empty($accessToken))
        {     
         //Log::info('in Middleware UserPageAccess -  cookies: '.json_encode($_COOKIE));
      
          // check if the cookie is set
          if (isset($_COOKIE[config('constants.ACCESSTOKEN_')])) 
          {
            $accessToken = $_COOKIE[config('constants.ACCESSTOKEN_')];
       //     Log::info('in Middleware UserPageAccess - token from cookie: ' . $accessToken.' cookies: '.json_encode($_COOKIE));
          }
        }
        //Log::info('in Middleware UserPageAccess - Authorization header: '.$accessToken);
      
        $user = User::getUserByAccessToken($accessToken);

        if ($user == null) 
        {
          //redirect to login
          //info('redirect to login user is not logged in');
          session()->flash('message', config('constants.UNAUTHORIZED'));
          return false;
        }
        else
        {
          \Auth::login($user);
        }
        return true;
   }

    /**
     * function is used to set accessToken cookie to browser
     */
    protected function setAccessTokenCookie($accessToken) {
        setcookie(config('constants.ACCESSTOKEN_'), $accessToken, time() + (86400 * 30), "/");
    }
}
