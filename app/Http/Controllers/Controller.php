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
use App\Models\MasterPage;
use Illuminate\Http\Request;

class Controller extends FrameworkController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $site; 

    public function __construct(Request $request)
    {
        
        $site = Controller::getClientFromHost();
        $this->site = $site;

        info('_construct Controller: '.$request->getRequestUri());
        $masterpage = $this->getMasterForSite($site);

        View::share('site', $site);
        View::share('masterPage', $masterpage);
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
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
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
      $dashboardpage = SitePages::where('fk_site_id',$site->id)->first();
      if ($dashboardpage != null)
      {    
          if (isset($dashboardpage->masterpage)){
              //pull the masterpage css and js and send this as well
              $masterpage = MasterPage::where('pagename',$dashboardpage->masterpage)->first();
          }
      }
      else{
          //this is called repeatedly. constantly. info('dashboard is null');
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
            session()->flash('status','You are not a member of this site.');
            return false;
        }
        return true;
    }
}
