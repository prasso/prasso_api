<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\SitePageService;
use App\Services\UserService;
use App\Models\SitePages;
use App\Models\MasterPage;
use App\Models\Site;
use App\Models\SitePageData;
use App\Models\User;

class SitePageController extends BaseController
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
        $masterpage = $this->getMaster($welcomepage);
      
        return view($welcomepage->masterpage) 
            ->with('sitePage',$welcomepage)            
            ->with('site',$this->site)
            ->with('page_short_url','/')
            ->with('masterPage',$masterpage);
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
            session()->flash('status',config('constants.LOGIN_AGAIN'));
            return redirect('/login');
        }
        
        // if the site supports registration, check to see if the site has a DASHBOARD site_page
        if ( $this->site != null && strcmp($this->site->site_name, config('app.name')) != 0)
        {
            $dashboardpage = SitePages::where('fk_site_id',$this->site->id)->where('section','Dashboard')->first();
            if ($dashboardpage != null)
            {    
                $dashboardpage->description = $this->prepareTemplate($dashboardpage);
                $masterpage = $this->getMaster($dashboardpage);
                return view($dashboardpage->masterpage)  
                ->with('sitePage',$dashboardpage)
                ->with('site',$this->site)
                ->with('page_short_url','/')
                ->with('masterPage',$masterpage);
            }
        }
        
        // if not, show the dashboard
        return view('dashboard');
    }

    private function getMaster($sitepage){
        $master_page = null;
        if (isset($sitepage->masterpage)){
            //pull the masterpage css and js and send this as well
            $master_page = MasterPage::where('pagename',$sitepage->masterpage)->first();
        }
        return $master_page;
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
    public function viewSitePage(Request $request,$section)
    {
        $user = Auth::user() ?? null;
        $sitepage = SitePages::where('fk_site_id',$this->site->id)->where('section',$section)->first();

        if ($sitepage == null)
        {
            Log::info('using system welcome: ');   
            return view('welcome');
        }
        //if the page requires admin, and the user isn't an admin, inform them no access to this page
        if ( $sitepage->pageRequiresAdmin() && $user != null && !$user->isInstructor())
        {
            return view('noaccess');
        }
        if ( ($sitepage->requiresAuthentication() && $user == null ) ||
                ($user != null && !$this->userService->isUserOnTeam($user)))
        {
            if ( $user == null ){
                \App\Http\Middleware\UserPageAccess::authorizeUser($request);
                $user = Auth::user() ?? null;   
                if ($user == null){
                    Auth::logout();
                    session()->flash('status',config('constants.LOGIN_AGAIN'));
                    return redirect('/login');
                }
            }
            if (($user != null && !$this->userService->isUserOnTeam($user))){
                Auth::logout();
                session()->flash('status',config('constants.LOGIN_AGAIN'));
                return redirect('/login');
            }
        }
        if ($sitepage->url != null && strlen($sitepage->url) > 5 && strcmp($sitepage->url,'http') != 0)
        {
            return redirect($sitepage->url);
        }

        $sitepage->description = $this->prepareTemplate($sitepage);
        $masterpage = $this->getMaster($sitepage);
        if ($sitepage->template != null && strlen($sitepage->template) > 0)
        {
            $page_content= $this->sitePageService->getTemplateData($sitepage);
            $sitepage->description = $page_content;
        }
        
        return view($sitepage->masterpage)//use the template here
            ->with('sitePage',$sitepage)
            ->with('site',$this->site)
            ->with('page_short_url','/page/'.$section)
            ->with('masterPage',$masterpage);
    }

     /**
     * Show the app edit form 
     *
     * @return \Illuminate\Http\Response
     */
    public function editSitePages($siteid)
    {
        if (!Controller::userOkToViewPageByHost($this->userService))
        {
            info('user not ok to view page: ' . $siteid);
            return redirect('/login');
        }
        $masterpage = Controller::getMasterForSite($this->site);
        
        return view('sitepage.view-site-pages')
            ->with('siteid', $siteid)
            ->with('site',$this->site)
            ->with('masterPage',$masterpage);
    }

    public function visualEditor($pageid)
    {
        if (!Controller::userOkToViewPageByHost($this->userService))
        {
            info('user not ok to view page: ' . $pageid);
            return redirect('/login');
        }
        $pageToEdit = SitePages::where('id',$pageid)->first();
        $master_page = $this->getMaster($pageToEdit);
        
        if ($pageToEdit == null)
        {
            session()->flash('status','Page not found.');
            return redirect()->back();
        }

        return view('sitepage.grapes-updated')->with('sitePage', $pageToEdit) ->with('site',$this->site)
        ->with('page_short_url','/page/'.$pageToEdit->section)->with('masterPage',$master_page);
    }

    /**this can be called from grapesjs editor. but the functionality is also
     * done in the visualeditor function above
     */
    public function getCombinedHtml($pageid)
    {
        if (!Controller::userOkToViewPageByHost($this->userService))
            {
                info('user not ok to view page: ' . $pageid);
                return redirect('/login');
            }
            $pageToEdit = SitePages::where('id',$pageid)->first();
            $pageToEdit->description = $this->prepareTemplate($pageToEdit);
        
            $master_page = $this->getMaster($pageToEdit);

            $pageToEdit->description = '<div id="core">'.$pageToEdit->description.'</div>'; // was $coreBlock
            if ($pageToEdit->style != null && strlen($pageToEdit->style) > 0)
            {
                $pageToEdit->description = $pageToEdit->description.'<style>'.$pageToEdit->style.'</style>'; // was $coreBlock
            
            }
            $masterPage = view($master_page->pagename)->with('sitePage',$pageToEdit)
            ->with('site',$this->site)
            ->with('page_short_url','/page/'.$pageToEdit->section)
            ->with('masterPage',$master_page)->render();

            return response()->json(['html' => $masterPage]);
            //return response()->json(['html' => $pageToEdit->description]);

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

    /**
     * a method used to store data from posted forms. 
     * the forms will be custom html stored in site_pages table and so this
     * will not depend on fixed form item names
     * the data will be stored in site_page_data table with siteid, data's key, json_data, and date created/updated
     */
    public function sitePageDataPost(Request $request){
        // make sure the user has access to this site and page
        if (!Controller::userOkToViewPageByHost($this->userService))
        {
            abort(403, 'Unauthorized action.');
        }

        $siteid = $request['siteid'];
        $pageid = $request['pageid'];
        $data_key = $request['data_key'];
        $newOne = false;
        if ($data_key == NULL){
            $data_key = uniqid();
            $newOne = true;
        }
        $page = SitePageData::where('fk_site_page_id',$pageid)->where('data_key', $data_key)->first();
        if ($page == null){
            $page = SitePageData::create(['fk_site_page_id'=>$pageid,'data_key'=>$data_key]);
        }
        else{
            // make sure this page belongs to this site
            if ($page->fk_site_id != $siteid){
                //save nothing
                return $this->sendResponse('mismatch in site', 'ok');
            }
        }
        //loop through the request and gather form input values to build json object
        $json = array();
        foreach ($request->all() as $key => $value) {
            if ($key != '_token' && $key != 'template' && $key != 'siteid' && $key != 'pageid'){
                $json[$key] = $value;
            }
        }
        $json = json_encode($json);
        $page->json_data = $json;
        $page->save();
        if ($newOne)
        {session()->flash('message','A freight order record has been added' );}
        else
        {session()->flash('message','A freight order record has been updated' );}
        return redirect()->back();

    }

}
