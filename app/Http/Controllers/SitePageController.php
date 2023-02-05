<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Providers\RouteServiceProvider;
use App\Services\SitePageService;
use App\Models\SitePages;
use App\Models\Site;

class SitePageController extends Controller
{
    protected $sitePageService;

    public function __construct(Request $request, SitePageService $sitePageService)
    {
        parent::__construct( $request);
        $this->sitePageService = $sitePageService;
    }
    /**
     * return welcome page if one is defined for this site
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $welcomepage = null;
        
        $user = Auth::user();
        if ($user != null)
        {
            if ($user->current_team_id == null) {
                 $user->current_team_id = $user->teams[0]->id;
                 $user->save(); 
            }
            
            // if the site supports registration, check to see if the site has a DASHBOARD site_page
            if ( $this->site != null && strcmp($this->site->site_name, config('app.name')) != 0)
            {
                $dashboardpage = SitePages::where('fk_site_id',$this->site->id)->where('section','Dashboard')->first();

                if ($dashboardpage != null)
                {    
                    //put in the csrf token
                    $dashboardpage->description = str_replace('CSRF_TOKEN', csrf_token(), $dashboardpage->description);
                    $dashboardpage->description = str_replace('USER_NAME', $user->name, $dashboardpage->description);
                    $dashboardpage->description = str_replace('USER_EMAIL', $user->email, $dashboardpage->description);
                       
                    return view('sitepage.masterpage')
                    ->with('sitePage',$dashboardpage);
                }
            }
            
            // if not, show the dashboard
            return view('dashboard');
        }
        
        if ( $this->site != null && strcmp($this->site->site_name, config('app.name')) != 0)
        {
            $welcomepage = SitePages::where('fk_site_id',$this->site->id)->where('section','Welcome')->first();
        }
        if ($welcomepage == null)
        {
            return view('welcome');
        }
        
        return view('sitepage.masterpage')
            ->with('sitePage',$welcomepage);
    }
    /**
     * return welcome page
     *
     * @return \Illuminate\Http\Response
     */
    public function viewSitePage($section)
    {
        $sitepage = SitePages::where('fk_site_id',$this->site->id)->where('section',$section)->first();

        if ($sitepage == null)
        {
            return view('welcome');
        }
        return view('sitepage.masterpage')
            ->with('sitePage',$sitepage);
    }
     /**
     * Show the app edit form 
     *
     * @return \Illuminate\Http\Response
     */
    public function editSitePages($siteid)
    {
        return view('sitepage.view-site-pages') ->with('siteid', $siteid);
    }

    public function visualEditor($pageid)
    {
        $pageToEdit = SitePages::where('id',$pageid)->first();
        return view('sitepage.grapes')->with('sitePage', $pageToEdit);
    }

    public function saveSitePage(Request $request)
    {
        $this->sitePageService->saveSitePage($request);
        return redirect()->back();
    }
}
