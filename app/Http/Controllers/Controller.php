<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as FrameworkController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use App\Models\Site;
use App\Models\SitePages;
use App\Models\User;
use App\Models\MasterPage;
use Illuminate\Http\Request;
use App\Mail\admin_error_notification;
use Illuminate\Support\Facades\Mail;

class Controller extends FrameworkController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $site; 
    public $masterpage;

    public function __construct(Request $request)
    {
        
        $site = Controller::getClientFromHost();
        $this->site = $site;
        if ($site == null)
        {
            Log::info('no site for this host. ');
            return;
        }

        // Skip setting masterpage for hosted sites
        if (!($site != null && !empty($site->deployment_path) && !empty($site->github_repository))) {
            $this->masterpage = $this->getMasterForSite($site);
            View::share('masterPage', $this->masterpage);
        }
        
        View::share('site', $site);
    }

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message)
    {
    	$response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 400)
    {
    	$response = [
            'success' => false,
            'message' => $error,
        ];

        if(!empty($errorMessages)){
            $response['error_message'] = json_encode($errorMessages);
        }

        return response()->json($response, $code);
    }

    public function adminNotifyOnError($message){
        //notify me admin error
        try{
            Mail::to('info@faxt.com', 'Prasso Admin')->send(new admin_error_notification($message));
        }catch(\Throwable $e){
            Log::info("Error sending email: {$message}");
            Log::info($e);
        }
    }

    /**
     * find the client from the host.
     * if no client for this host, send to the default 404.
     *
     */
    public static function getClientFromHost()
    {
        $host = request()->getHttpHost();
            
        $site = Site::getClient($host);
        if ($site == null || !isset($site) )
        {
            abort(404);
            return null;
        }
        return $site;
    }

    public static function getMasterForSite($site){

      $masterpage = null;
      
      // Check if this is a GitHub hosted site
      if ($site != null && !empty($site->deployment_path) && !empty($site->github_repository)) {
          // For GitHub hosted sites, we don't need a masterpage as the site has its own layout
          // Return null to indicate no masterpage is needed
          return $masterpage;
      }
      
      // For regular sites, continue with the existing logic
      $dashboardpage = SitePages::where('fk_site_id',$site->id)->first();
      if ($dashboardpage != null)
      {    
          if (isset($dashboardpage->masterpage)){
              //pull the masterpage css and js and send this as well
              $masterpage = MasterPage::where('pagename',$dashboardpage->masterpage)->first();
          }
      }
      else{
           info('dashboard is null');
      }
      return $masterpage;
    }

    /**
     * check if the user is on the team for the site.
     * This code uses the HOST to determine the site. NOT the site id in the url.
     * if not, log them out and send them to the login page.
     *
     */
    public static function userOkToViewPageByHost($userService)
    {
        $site = Controller::getClientFromHost();
        if ($site == null)
        {
           session()->flash('status','Unknown site, if just created wait a bit for the Internet to realize it exists.');
           return false;
        }

        if (\Auth::user()==null || !$userService->isUserOnTeam(\Auth::user()))
        {
            \Auth::logout();
            session()->flash('status',config('constants.LOGIN_AGAIN'));
            return false;
        }
        return true;
    }

    // use for debugging data together with callstack info
    public static function dd_with_callstack(...$args) {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $caller = array_shift($trace);
        $caller_str = $caller['file'] . ':' . $caller['line'];
        $callstack = array_map(function($trace) {
            return $trace['file'] . ':' . $trace['line'];
        }, $trace);
        array_unshift($args, $callstack);
        array_unshift($args, $caller_str);
        dd(...$args);
    }

    protected function setUpUser($request,$user)
    {
        $accessToken = $request->header(config('constants.AUTHORIZATION_'));
        //if no accesstoken, check if we have an X-Authorization header present
        if($accessToken == '' && $auth = $request->header(config('constants.XAUTHORIZATION_'))) {
            info('setting authorization header from xauthorization header');
            $request->headers->set('Authorization', $auth);
        }

        $accessToken = str_replace("Bearer ","",$accessToken);
    
        if (!isset($accessToken) && isset($_COOKIE[config('constants.AUTHORIZATION_')]))
        {
            $accessToken = $_COOKIE[config('constants.AUTHORIZATION_')];
        }
        else {

            if ((!isset($accessToken) || $accessToken == 'Bearer') && $user != null) 
            {

                $accessToken = $request->user()->createToken(config('app.name'))->accessToken->token;

            }
        }
        if (isset($accessToken))
        {
            $this->setAccessTokenCookie($accessToken);
            if ($user == null)
            {
                $user = User::getUserByAccessToken($accessToken);
            }

            if ($user != null) 
            {
                \Auth::login($user); 
            }
        }
       
       return $user;
    }

        /**
    * function is used to accessToken email cookie to browser
    */
    protected function unsetAccessTokenCookie()
    {
        setcookie(config('constants.ACCESSTOKEN_'), '', time() - 3600, "/"); 
    }

    /**
     * function is used to set accessToken cookie to browser
     */
    protected function setAccessTokenCookie($accessToken)
    {
        setcookie(config('constants.ACCESSTOKEN_'), $accessToken, time() + (86400 * 30), "/");
        
        setcookie(config('constants.COMMUNITYTOKEN'), $accessToken, time() + (86400 * 30), "/");
        setcookie(config('constants.COMMUNTIYREMEMBER'), $accessToken, time() + (86400 * 30), "/");
    }
}
