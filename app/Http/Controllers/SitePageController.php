<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Providers\AppServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\SitePageService;
use App\Services\UserService;
use App\Models\SitePages;
use App\Models\PageView;
use App\Models\MasterPage;
use App\Models\Site;
use App\Models\SitePageData;
use App\Models\SitePageTemplate;
use Illuminate\Support\Str;
use App\Models\User;
use Livewire\Livewire;
use App\Http\Livewire\HeaderCarousel;
use Livewire\Mechanisms\ComponentRegistry;

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
    public function loadLiveWireComponent($component,$pageid)
    {
        return view('sitepage.site-page-component')->with('component',$component)->with('pageid',$pageid); 
    }
     /**
     * this code verifies that the user is a member of the current site's team
     * loads up the dashboard if the user is logged in and belongs to this site's team
     */
    private function getDashboardForCurrentSite($user){

        $user_content = $this->getPage('Dashboard',$user);
        // Render the dashboard view with either custom content or default content
        return view('dashboard')->with('user_content', $user_content)           
        ->with('site',$this->site);
    }
    
    private function getPage($page, $user){

        $user->setCurrentToOwnedTeam();
        $request = Request::capture();
    
        if ( !$this->userService->isUserOnTeam($user) )
        {
            Auth::logout();
            session()->flash('status',config('constants.LOGIN_AGAIN'));
            return redirect('/login');
        }
        $user_content='';
    
        // if the site supports registration, check to see if the site has a $page site_page
        // Check if the current site exists and is not the main application site
        if ($this->site !== null && strcmp($this->site->site_name, config('app.name')) !== 0) {
            // Try to find a custom page for the current site
            $pageFound = SitePages::where('fk_site_id', $this->site->id)
                ->where('section', $page)
                ->first();

            // If a custom page exists, prepare its content
            if ($pageFound !== null) {    
                $user_content = $this->prepareTemplate($pageFound, $request->path());
            }
        }
        // return the prepared content
        return $user_content;
    }


    private function getMaster($sitepage) {
        $masterPage = null;
        
        if (isset($sitepage->masterpage)) {
            // If the sitepage has a masterpage specified, use it
            $masterPage = MasterPage::where('pagename', $sitepage->masterpage)->first();
        } elseif (isset($this->masterpage)) {
            // If the sitepage doesn't have a masterpage, use controller's masterpage if available
            $masterPage = MasterPage::where('pagename', $this->masterpage)->first();
        }
        
        return $masterPage;
    }

    private function prepareTemplate($pageToProcess, $path=null){
        
        $page_content = $pageToProcess->description;
        $user = Auth::user() ?? null;
        //First,Check if the header placeholder exists, 
        //then replace it with the header page if defined
        if (strpos($page_content, '[HEADER]') !== false) {
            //the header text that will be placed into $page_content
            // is an actual page for this site. a page that has the title of [HEADER]
            $headerPage = SitePages::where('fk_site_id', $this->site->id)
                ->where('title', '[HEADER]')->first();
            if ($headerPage !== null) {
            $page_content = str_replace('[HEADER]', $headerPage->description, $page_content);
            }
        }
        //replace the tokens in the dashboard page with the user's name, email, and profile photo
        $page_content = str_replace('CSRF_TOKEN', csrf_token(), $page_content);
        $page_content = str_replace('[TEAM_ID]', $this->site->teamFromSite()->id, $page_content);
        $page_content = str_replace('MAIN_SITE_COLOR', $this->site->main_color, $page_content);
        $page_content = str_replace('[SITE_CSS]', $this->site->app_specific_css, $page_content);
        $page_content = str_replace('SITE_MAP', $this->site->getSiteMapList($path), $page_content);
        $page_content = str_replace('SITE_NAME', $this->site->site_name, $page_content);
        $page_content = str_replace('SITE_LOGO_FILE', $this->site->logo_image, $page_content);
        $page_content = str_replace('SITE_FAVICON_FILE', $this->site->favicon, $page_content);
        $page_content = str_replace('SITE_DESCRIPTION', $this->site->description, $page_content);
        $page_content = str_replace('PAGE_NAME', $pageToProcess->title, $page_content);
        $page_content = str_replace('PAGE_SLUG', $pageToProcess->section, $page_content);
        $page_content = str_replace('[SITE_ID]',$this->site->id, $page_content);
        $page_content = str_replace('[DATA_PAGE_ID]',$pageToProcess->id, $page_content);
        
        
        //Check if the carousel placeholder exists, then replace it with the Livewire component
        if (strpos($page_content, '[CAROUSEL_COMPONENT]') !== false) {

            $page_content = str_replace(
                '[CAROUSEL_COMPONENT]',
                '<div id="carouseldiv"></div>
                <script>
                document.addEventListener("DOMContentLoaded", function() {
                    loadLivewireComponent("prasso-flipper", "carouseldiv", '.$pageToProcess->id.');
                });</script>', 
                $page_content
            );
        }

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
            //check for privacy and for terms of service, if shown then provide the Prasso version
            $matches = array_filter(AppServiceProvider::$allowedUris, function ($allowedUri) use ($section) {
                return strpos($section, $allowedUri) !== false;
            });
            if (count($matches) > 0) {
                AppServiceProvider::loadDefaultsForPagesNotUsingControllerClass($this->site);
                return view($section);
            }
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
            $page_content='';
            if ($site_page_data == null)
            {
                //multi-record result sets will be returned within the x-data attribute of the template in $page_content
                $page_content= $this->sitePageService->getTemplateData($sitepage, $placeholder, $user, $this->site);
               
            }
            else{

                $template_data = SitePageTemplate::where('templatename', $sitepage->template)->first();
                $jsonData = $this->sitePageService->processJSONData($site_page_data, $template_data, $this->site);
               
                $page_content = str_replace($placeholder, $jsonData, $sitepage->description);
            }
            $sitepage->description = $page_content;
            //Controller::dd_with_callstack($sitepage);
        
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
            info('user not ok to view page. editSitePages ' . $siteid);
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
            info('user not ok to view page in visualEditor: ' . $pageid);
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
                info('user not ok to view page in getcombinedhtml: ' . $pageid);
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
        info('Livestream Activity: ' . $request->body);
        $receipient_user = \App\Models\User::where('id',1)->first();
        $receipient_user->sendLivestreamNotification('Livestream Notification', $request->body, $receipient_user->email, $receipient_user->name);
        
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
    public function sitePageDataPost(Request $request,$siteid,$pageid){
        // make sure the user has access to this site and page
        if (!Controller::userOkToViewPageByHost($this->userService))
        {
            abort(403, 'Unauthorized action.');
        }
        $id = $request->input('id');
        $team_id = $request->input('team_id');
        $data_key = $request->input('data_key');

        $newOne = false;
        
        //if id is in the data, use it to find this record.
        $data = null;
        if ($id) {
            $data = SitePageData::where('id', $id)->first(); // Change from get() to first()
        } elseif ($data_key) {
            $data = SitePageData::where('fk_site_page_id', $pageid)->where('data_key', $data_key)->first();
        }
        
        if ($data_key == NULL){
            $data_key = Str::uuid();
            $newOne = true;
        }
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
        $data->fk_team_id = $team_id;
        $data->save();

        if ($request['return_json']=='true'){
           
            //return all json with this pageid from site page data if that value is true
            $site_page = SitePages::find($pageid);
            $site = Site::find($siteid);
            $jsonData = $this->sitePageService->getTemplateDataJSON($site_page,  Auth::user() ?? null, $site);

            $message = 'success';
            return $this->sendResponse($jsonData, $message) ;
        
        }

        //page didn't request json return, redirect back with a message.
        $page = SitePages::find($pageid);
        if ($newOne)
        {$message = 'A new '.$page->section.' record has been added';}
        else
        {$message = 'A '.$page->section.' record has been updated';}
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
            $json_data= $this->sitePageService->getTemplateDataJSON($sitepage, $user, $this->site);
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
            $data_key = Str::uuid();
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
    
    /*/v1/sites/" + n.site_name + "/page_views*/
    public function pageView(Request $request, $site_name)
    {
        // Get the page ID from the request
        $page_name = json_encode($request->input('page_view_attributes'));
       // info('request vars: '. json_encode($request->all()));

        // Create a new PageView instance
        $pageView = new PageView();
    
        // Set the page ID and site ID for the page view
        $pageView->page_name = $page_name;
        $pageView->site_name = $site_name;
    
        // Save the page view to the database
        $pageView->save();
    
        // Return a response indicating success
        return response()->json(['message' => 'OK'], 200);
    }

    public function editSitePageJsonData($siteId, $sitePageId)
    {
        // make sure the user has access to this site and page
        if (!Controller::userOkToViewPageByHost($this->userService))
        {
            info('no access for this user. editSitePageJsonData: ' .  $sitePageId);
            abort(403, 'Unauthorized action.');
        }

        $sitePage = SitePages::findOrFail($sitePageId);
        $sitePageData = SitePageData::where('fk_site_page_id',$sitePageId)->first();
// Check if the site page data is null
if (is_null($sitePageData)) {
    // Set a session flash message
    session()->flash('message', 'The page has no data defined for it.');
    
        // Redirect back to the previous page with the flash message
        return redirect()->back();
        }
        $jsonData = json_decode($sitePageData->json_data, true);

        return view('sitepage.edit-site-page-json-data', [
            'siteId' => $siteId,
            'sitePage' => $sitePage,
            'sitePageDataid' => $sitePageData->id,
            'jsonData' => $jsonData ?? []
        ]);
    }

    public function updateSitePageJsonData(Request $request, $siteId, $sitePageDataId)
    {
         // make sure the user has access to this site and page
         if (!Controller::userOkToViewPageByHost($this->userService))
         {
             info('no access for this user. updateSitePageJsonData: $sitePageDataId ' .  $sitePageDataId);
             abort(403, 'Unauthorized action.');
         }

        $sitePageData = SitePageData::findOrFail($sitePageDataId);
        $updatedJsonData = $request->input('json_data');

        // Ensure the updated data is encoded back to JSON
        $sitePageData->json_data = json_encode($updatedJsonData);
        $sitePageData->save();

        return redirect()->route('sitepages.editSitePageJsonData', [$siteId, $sitePageData->fk_site_page_id])
                         ->with('success', 'Site page json data updated successfully!');
    }

    // Add a new method to handle the deletion of a site page data item
    public function deleteSitePageJsonData($siteId, $sitePageId, $dataId)
    {
        // Find the site page data item by ID and delete it
        $sitePageData = SitePageData::find($dataId);
        if ($sitePageData) {
            $sitePageData->delete();
        }

        // Redirect back to the edit page with a success message
        return redirect()->route('sitepages.edit-site-page-json-data', ['siteId' => $siteId, 'sitePageId' => $sitePageId])
                         ->with('success', 'Item deleted successfully.');
    }
}
