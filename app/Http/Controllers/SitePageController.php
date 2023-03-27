<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\SitePageService;
use App\Services\UserService;
use App\Models\SitePages;
use App\Models\Site;
use App\Models\User;

class SitePageController extends Controller
{
    protected $sitePageService;
    protected $userService;

    public function __construct(Request $request, SitePageService $sitePageService,
                                UserService $userServ)
    {
        parent::__construct( $request);
        $this->sitePageService = $sitePageService;
        $this->userService = $userServ;
    }

    /**
     * return welcome page if one is defined for this site
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $welcomepage = null;
        
        $user = Auth::user() ?? null;
        if ($user != null)
        {
            return $this->getDashboardForCurrentSite($user);
        }
        
        if ( $this->site != null && strcmp($this->site->site_name, config('app.name')) != 0)
        {
            $welcomepage = SitePages::where('fk_site_id',$this->site->id)->where('section','Welcome')->first();
        }
        if ($welcomepage == null)
        {
            return view('welcome');
        }
        $welcomepage->description = $this->prepareTemplate($welcomepage);
        return view($welcomepage->masterpage) 
            ->with('sitePage',$welcomepage);
    }

    /**
     * this code verifies that the user is a member of the current site's team
     * loads up the dashboard if the user is logged in and belongs to this site's team
     */
    private function getDashboardForCurrentSite($user){
        
        $user->setCurrentTeam();
       
        if ( !$this->userService->isUserOnTeam($user) )
        {
            Auth::logout();
            session()->flash('status','You are not a member of this site.');
            return redirect('/login');
        }
        
        // if the site supports registration, check to see if the site has a DASHBOARD site_page
        if ( $this->site != null && strcmp($this->site->site_name, config('app.name')) != 0)
        {
            $dashboardpage = SitePages::where('fk_site_id',$this->site->id)->where('section','Dashboard')->first();
            if ($dashboardpage != null)
            {    
                $dashboardpage->description = $this->prepareTemplate($dashboardpage);
                return view($dashboardpage->masterpage)  
                ->with('sitePage',$dashboardpage);
            }
        }
        
        // if not, show the dashboard
        return view('dashboard');
    }

    private function prepareTemplate($dashboardpage){
        $page_content = $dashboardpage->description;
        $user = Auth::user() ?? null;

        //replace the tokens in the dashboard page with the user's name, email, and profile photo
        $page_content = str_replace('CSRF_TOKEN', csrf_token(), $page_content);
        $page_content = str_replace('MAIN_SITE_COLOR', $this->site->main_color, $page_content);
        $page_content = str_replace('SITE_MAP', $this->site->getSiteMapList(), $page_content);
        $page_content = str_replace('SITE_NAME', $this->site->site_name, $page_content);
        $page_content = str_replace('SITE_LOGO_FILE', $this->site->logo_image, $page_content);
        $page_content = str_replace('SITE_FAVICON_FILE', $this->site->favicon, $page_content);
        $page_content = str_replace('SITE_DESCRIPTION', $this->site->description, $page_content);
        $page_content = str_replace('PAGE_NAME', $dashboardpage->title, $page_content);
        $page_content = str_replace('PAGE_SLUG', $dashboardpage->section, $page_content);

        if ($user != null){
            $page_content = str_replace('USER_NAME', $user->name, $page_content);
            $page_content = str_replace('USER_EMAIL', $user->email, $page_content);
            $page_content = str_replace('USER_PROFILE_PHOTO', $user->getProfilePhoto(), $page_content);
        }
  
        return $page_content;      
    }

    /**
     * return welcome page
     *
     * @return \Illuminate\Http\Response
     */
    public function viewSitePage($section)
    {
        $user = Auth::user() ?? null;
        $sitepage = SitePages::where('fk_site_id',$this->site->id)->where('section',$section)->first();

        if ($sitepage == null)
        {
Log::info('using system welcome: ');   
            return view('welcome');
        }
        if ( ($sitepage->requiresAuthentication() && $user == null ) ||
                ($user != null && !$this->userService->isUserOnTeam($user)))
        {
            Auth::logout();
            session()->flash('status','You are not a member of this site.');
            return redirect('/login');
        }

        $sitepage->description = $this->prepareTemplate($sitepage);
      
        return view($sitepage->masterpage)//use the template here
            ->with('sitePage',$sitepage)
            ->with('site',$this->site)
            ->with('page_short_url','/page/'.$section);
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
        
        if ($pageToEdit == null)
        {
            session()->flash('status','Page not found.');
            return redirect()->back();
        }
        return view('sitepage.grapes')->with('sitePage', $pageToEdit);
    }

    public function saveSitePage(Request $request)
    {
        $this->sitePageService->saveSitePage($request);
        return redirect()->back();
    }


    /**
     * Livestream activity from AWS EventStream
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function livestream_activity(Request $request){
        //ship this off to the logic that processes emails
        info('Livestream Activity: ' . $request['detail']['event_name']);
        $receipient_user = \App\Models\User::where('id',1)->first();
        $receipient_user->sendLivestreamNotification('Livestream Notification', $request['detail']['event_name'], $receipient_user->email, $receipient_user->name);
        
    }

    public function giveToDonate(){
        
        return redirect()->to('/page/donate');
    }

}
