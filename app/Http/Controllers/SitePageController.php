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
use App\Models\SitePageTemplate;
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
        $request = Request::capture();
        
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
        $welcomepage->description = $this->prepareTemplate($welcomepage, $request->path());
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
        $request = Request::capture();
       
        if ( !$this->userService->isUserOnTeam($user) )
        {
            Auth::logout();
            session()->flash('status',config('constants.LOGIN_AGAIN'));
            return redirect('/login');
        }
        $user_content='';
        // if the site supports registration, check to see if the site has a DASHBOARD site_page
        if ( $this->site != null && strcmp($this->site->site_name, config('app.name')) != 0)
        {
            $dashboardpage = SitePages::where('fk_site_id',$this->site->id)->where('section','Dashboard')->first();
            if ($dashboardpage != null)
            {    
                $user_content = $this->prepareTemplate($dashboardpage, $request->path());
            }
        }
        return view('dashboard')->with('user_content', $user_content);
    }

    private function getMaster($sitepage){
        $master_page = null;
        if (isset($sitepage->masterpage)){
            //pull the masterpage css and js and send this as well
            $master_page = MasterPage::where('pagename',$sitepage->masterpage)->first();
        }
        return $master_page;
    }

    private function prepareTemplate($dashboardpage, $path=null){
        
        $page_content = $dashboardpage->description;
        $user = Auth::user() ?? null;

        //replace the tokens in the dashboard page with the user's name, email, and profile photo
        $page_content = str_replace('CSRF_TOKEN', csrf_token(), $page_content);
        $page_content = str_replace('MAIN_SITE_COLOR', $this->site->main_color, $page_content);
        $page_content = str_replace('SITE_MAP', $this->site->getSiteMapList($path), $page_content);
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
        if ($section == 'favicon.ico')
        {
            abort(404);
            return null;
        }
        $user = Auth::user() ?? null;
        if ($user == null)
        {
            $user = $this->setUpUser($request, $user);
        }
        $sitepage = SitePages::where('fk_site_id',$this->site->id)->where('section',$section)->first();

        if ($sitepage == null)
        {

            info('viewSitePage page not found, using system welcome: '.$section.' site:'.$this->site->id); 
            return view('welcome');
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
        //if the page requires admin, and the user isn't an admin, inform them no access to this page
        if ( $sitepage->pageRequiresAdmin() && $user != null && !$user->isInstructor())
        {
            return view('noaccess');
        }
        if ($sitepage->url != null && strlen($sitepage->url) > 5 && strcmp($sitepage->url,'http') != 0)
        {
            return redirect($sitepage->url);
        }
        $nodata = null;
       return $this->prepareAndReturnView($sitepage, $nodata, $user, $request);
        
    }

    /**
     * This is the method that is called when a user clicks on a link to edit a site page's data 
     * using that pages' form
     */
    public function editSitePageData(Request $request,$section, $dataid)
    {
        if ($section == 'favicon.ico')
        {
            abort(404);
            return null;
        }
        $user = Auth::user() ?? null;   
        if ($user == null){
            \App\Http\Middleware\UserPageAccess::authorizeUser($request);
            if ($user == null){
                Auth::logout();
                session()->flash('status',config('constants.LOGIN_AGAIN'));
                return redirect('/login');
            }
        }
        //make sure user has access to edit
        if (!$this->userService->isUserOnTeam($user))
        {
            info('user is not on team to edit site page data '.$user->id);
            abort(404);
        }
        //get record id'd by dataid
        $data = SitePageData::where('id',$dataid)
                ->firstOr(function () {
                    return null;
                });
        if ($data == null){
            info('data id not found in site page data table. '.$dataid);
            abort(404);
        }

        //get view id'd in data record
        $sitepage = SitePages::where('id',$data->fk_site_page_id)
                        ->where('section',$section)
                        ->firstOr(function () {
                            return null;
                        });

        if ($sitepage == null)
        {
            info('page id not found from data '.$data->fk_site_page_id);
            abort(404);
        }

        //put data in form
        return $this->prepareAndReturnView($sitepage, $data, $user, $request);
    }

    private function prepareAndReturnView($sitepage, $site_page_data, $user, $request){
        $sitepage->description = $this->prepareTemplate($sitepage, $request->path());
        $masterpage = $this->getMaster($sitepage);

        $placeholder = '[DATA]';
        if ($sitepage->template != null && strlen($sitepage->template) > 0 && strpos($sitepage->description, '[DATA]') !== false)
        {
            if ($site_page_data == null)
            {$page_content= $this->sitePageService->getTemplateData($sitepage, $placeholder, $user);}
            else{

                $template_data = SitePageTemplate::where('templatename', $sitepage->template)->first();
                $jsonData = $this->sitePageService->processJSONData($site_page_data, $template_data);
               
                $page_content = str_replace($placeholder, $jsonData, $sitepage->description);
            }
            $sitepage->description = $page_content;
        }

        return view($sitepage->masterpage)//use the template here
            ->with('sitePage',$sitepage)
            ->with('site',$this->site)
            ->with('page_short_url','/page/'.$sitepage->section)
            ->with('masterPage',$masterpage);
    }
     /**
     * Show the app edit form 
     *
     * @return \Illuminate\Http\Response
     */
    public function editSitePages($siteid)
    {
        if ($siteid == 'favicon.ico')
        {
            abort(404);
            return null;
        }
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
        $page_site = Site::where('id',$pageToEdit->fk_site_id)->with('teams')->first();
        $team_images = \App\Models\TeamImage::where('team_id', $page_site->teams[0]->id)->pluck('path')
                        ->map(function ($path) {
                            return config('constants.CLOUDFRONT_ASSET_URL') . $path;
                        });
         
        if ($pageToEdit == null)
        {
            session()->flash('status','Page not found.');
            return redirect()->back();
        }

        return view('sitepage.grapes-updated')
            ->with('sitePage', $pageToEdit) 
            ->with('site',$this->site)
            ->with('team_images', $team_images)
            ->with('page_short_url','/page/'.$pageToEdit->section)
            ->with('masterPage',$master_page);
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

        $team_id = $request['team_id'];
        $pageid = $request['pageid'];
        $data_key = $request['data_key'];
        $newOne = false;
        if ($data_key == NULL){
            $data_key = uniqid();
            $newOne = true;
        }
        $data = SitePageData::where('fk_site_page_id',$pageid)->where('data_key', $data_key)->first();
        if ($data == null){
            $data = SitePageData::create(['fk_site_page_id'=>$pageid,'data_key'=>$data_key, 'fk_team_id'=>$this->site->teams[0]->id]);
        }
        else{
            // make sure this page belongs to this site
            if ($data->fk_team_id != $team_id){
                $message = 'No changes due to mismatch in team: ' . $team_id . ' ' . $data->fk_team_id;
                session()->flash('message',$message );
                //save nothing
                redirect()->back();
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
        $data->json_data = $json;
        $data->save();

        if ($newOne)
        {$message = 'A freight order record has been added';}
        else
        {$message = 'A freight order record has been updated';}
        if (SitePages::pageNotificationsRequested($pageid)){
            //user wants notifications when this data changes
            $user = \Auth::user();
            $user->sendDataUpdated($user, $this->site);
        }
        session()->flash('message',$message );
        return redirect()->back();

    }

    public function lateTemplateData(Request $request){
        $pageid = $request['pageid'];
        // make sure the user has access to this site and page
        if (!Controller::userOkToViewPageByHost($this->userService))
        {
            info('no access for this user. late template data: ' . $pageid);
            abort(403, 'Unauthorized action.');
        }

        $user = Auth::user() ?? null;
        $sitepage = SitePages::where('fk_site_id',$this->site->id)->where('id',$pageid)->first();

        if ($sitepage == null)
        {
            Log::info('lateTemplateData, page not found. using system welcome: ');   
            return json_encode(['data' => '']);
        }

        if ( ($sitepage->requiresAuthentication() && $user == null ) ||
                ($user != null && !$this->userService->isUserOnTeam($user)))
        {
            if ( $user == null ){
                \App\Http\Middleware\UserPageAccess::authorizeUser($request);
                $user = Auth::user() ?? null;   
                json_encode(['data' => '']);
            }
            if (($user != null && !$this->userService->isUserOnTeam($user))){
                json_encode(['data' => '']);
            }
        }
        //if the page requires admin, and the user isn't an admin, inform them no access to this page
        if ( $sitepage->pageRequiresAdmin() && $user != null && !$user->isInstructor())
        {
            return json_encode(['data' => '']);
        }

        if ($sitepage->template != null && strlen($sitepage->template) > 0) 
        {
            $json_data= $this->sitePageService->getTemplateDataJSON($sitepage, $user);
            return  $json_data;
        }
        
        return json_encode(['data' => '']);
        
    }

    public function readTsvIntoSitePageData(Request $request){
        
        $pageid = $request['pageid'];
        // todo update this code to take a passed in file. for now, just use data.tsv
        $file = fopen('data.tsv', 'r');

        //ToDo: check if this site page has a data import defined.
        // if no data import defined then iterate the tabs and
        // do the key value pair thing as seen below

        // Loop through each line of the file
        while (($line = fgets($file)) !== false) {
            $data_key = uniqid();
            $page = SitePageData::create(['fk_site_page_id'=>$pageid,'data_key'=>$data_key]);
            // Split the line into an array of values
            $values = explode("\t", $line);

            $json = array();
            foreach ($values as $key => $value) {
                $json["column$key"] = $value;
            }
            
            $json = json_encode($json);
           // info($json);
            $page->json_data = $json;
            $page->save();
        }

        // Close the file
        fclose($file);

    }

}
