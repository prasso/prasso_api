<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AppsService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Models\Apps;
use App\Models\Team;
use App\Models\Notifications;
use App\Models\Tabs;
use App\Models\Site;
use App\Models\UserActiveApp;
use App\Models\FlutterIcons;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class TeamController extends Controller
{

    public function __construct(Request $request)
    {
        parent::__construct( $request);
        $this->middleware('instructorusergroup');

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($teamid)
    {
        $user = Auth::user();

        // Load team from URL, including apps
        $team = Team::where('id', (int) $teamid)->with('apps')->firstOrFail();

        // Authorization: super admins can access any team; others must be owners or members
        $isSuperAdmin = $user->isSuperAdmin();
        $isOwner = ($team->user_id === $user->id);
        $isMember = \App\Models\TeamUser::where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->where('role', 'instructor')
            ->exists();

        if (!$isSuperAdmin && !$isOwner && !$isMember) {
            abort(403, 'Unauthorized.');
        }

        // Apps for the requested team
        $teamapps = $team->apps;

        // Active app for the current user (used for mobile activation UX)
        $activeApp = UserActiveApp::where('user_id', $user->id)->first();
        $activeAppId = isset($activeApp->app_id) ? $activeApp->app_id : '0';

        // Teams list for the page's selector:
        // - Super admin: show just the requested team to keep context clear
        // - Others: show their owned teams (existing behavior)
        $teams_owned = $isSuperAdmin ? collect([$team]) : $user->team_owner;

        return view('apps.show')
            ->with('user', $user)
            ->with('teams', $teams_owned)
            ->with('teamapps', $teamapps)
            ->with('team', $team)
            ->with('activeappid', $activeAppId);
    }
    
    /**
     * create new site and app with a wizard
     *
{{ ... }}
     * @return \Illuminate\Http\Response
     */
    public function newSiteAndApp()
    {
        $user = Auth::user(); 
        $team = Team::where('id',$user->current_team_id)->first();
        if ($team == null){
            throw new \Exception('newSiteAndApp: Team not found in current_team_id');
        }

        $team_selection = $team->pluck('name','id');
        return view('apps.new-site-wizard')
            ->with('user', $user)
            ->with('team', $team)
            ->with('team_selection', $team_selection);
    }
    
    public function editTeam($teamid)
    {
        $user = Auth::user(); 
        if (!$user->isSuperAdmin() && !$user->isTeamOwnerForSite($this->site))
        {
            abort(403, 'Unauthorized action.');
        }

        $team = Team::where('id',$teamid)->with('site')->with('users')->first();
        return view('teams.show')->with('team', $team);
    }

    /**
     * show the team messages form
     * messages to users ( pn's )
     * users will be selected and the pns will be scheduled
     */
    public function setupForTeamMessages($teamid, Request $request) {

        $user = Auth::user();
       // Log::info('In setupForTeamMessages');
        $user_access_token = isset($user->personalAccessToken) ? $user->personalAccessToken : null;

        $team = $user->team_owner->where('id', $teamid)->first();

        if (count($team->users)>0)
        {
            $recipients = $team->users->sortBy('name');
        }
        else
        {
            $recipients = [];
        }
        $user_email='';
        if (isset($request->user_email))
        {
            $user_email = $request->user_email;
        }
        $formdata['notifications'] = new Notifications();
        $formdata['recipients'] = $recipients;
        $formdata['team'] = $team;
        //get the team members and the user info and send back to the form
        return view('teams.team-messages')
        ->with('user', $user)
            ->with('formdata', $formdata)
            ->with('user_email',$user_email)
            ->with('access_token', $user_access_token)
            ->with('message','');
    }

    /**
     * process the messages, schedule them and return the form with messages
     */
    public function processTeamMessages(Request $request, $teamid) 
    {

        $input = $request->all();
        $user = Auth::user();

        // MAKE THIS WORK TO EITHER SEND PUSH NOTIFICATIONS OR AN EMAIL
        // OR A TXT MESSAGE IF THE USER'S PROFILE HAS A PHONE NUMBER
    
        $is_email_request=false;
        $is_pn_request=false;
        $is_sms_request=false;
        if (isset($input['emailselections']) && $input['emailselections'] == 'email' )
        {
            $is_email_request=true;
        }
        else if (isset($input['emailselections']) && $input['emailselections'] == 'sms' )
        {
            $is_sms_request=true;
        }
        else
        {
            $is_pn_request=true;
        }
        $sendto = [];
        $notify = new Notifications();
        $notify->user_sender = $user->id;
        if (isset($input['subject']) )
        {
            $notify->subject = $input['subject'];
            $notify->body = $input['body'];
        }
        if (isset($input['emailToSend']) )
        {
            $is_email_request = true;
            $notify->emailToSend = $input['emailToSend'];
        }

        //convert the user's input time to UTC. assume this comes in from their timezone
        $notify->schedule_date_time = \DateTime::createFromFormat('Y-m-d H:i', $input['schedule_date_time'], new \DateTimeZone($user->timeZone));
        $notify->action= $input['emailselections'];

        foreach($input as $formitem)
        {
            if ($formitem != null && str_starts_with($formitem, 'member-')  )
            {
                $aruserId = explode ( '-', $formitem);
                $sendto[] = $aruserId[1];
            }
        }
        
      
            foreach($sendto as $userid)
            {
                if ($is_pn_request)
                {
                    $blank_notify =   $notify->replicate();
                    $blank_notify->user_receiver = $userid;
                    $blank_notify->save();
                }
                else {
                     //ship this off to the logic that processes emails
                   $receipient_user = \App\Models\User::where('id',$userid)->first();
                    
                    if ($is_email_request)
                    {
                    //ship this off to the logic that processes emails
                    $receipient_user->sendCoachEmail($input['subject'], $input['body'], $user->email, $user->name);
                    }
                    if ($is_sms_request)
                    {
                    info('sending text message: '.$user->phone);
                    
                    $team = Team::where('id',$user->current_team_id)->first();
     
                    info($team->phone);
                    $fromphone = $team->phone;
                    if (!isset($fromphone))
                    {
                        $fromphone = getenv("TWILIO_NUMBER");
                    }
                    info('FROM : '.$fromphone);
                    
                    $receipient_user->sendCoachSms( $input['body'], $fromphone, $receipient_user->phone);
                    }
                }
            }
        
        
        session()->flash(
            'message',
            'Messages have been scheduled.'
        );
        
        return redirect()->back();
    }

    

    /**
     * Show the app edit form 
     *
     * @return \Illuminate\Http\Response
     */
    public function editApp(AppsService $appsService,$teamid, $appid)
    {
        $user = Auth::user(); 
        // Authorize by membership/ownership instead of requiring current_team_id match
        if (!$user || !$user->isTeamMemberOrOwner((int) $teamid)) {
            return $this->sendError('Unauthorized.', ['error' => 'You do not have access to this team.'], 403);
        }
        $team = Team::where('id',$teamid)->first();
        $teamapps = $team->apps;     
        $teamapp = $teamapps->where('id',$appid)->first();
        // Build a selection list from teams the user owns
        $team_selection = $user->team_owner ? $user->team_owner->pluck('name','id') : collect([$team->id => $team->name]);
        if ($appid == 0 || $teamapp ==  null)
        {
            $teamapp = $appsService->getBlankApp($user);
            // Pre-fill the team for the new app so the form has context
            $teamapp->team_id = (int) $teamid;
            // No tabs yet for a new/blank app
            $apptabs = collect();
        }
        else {
            $apptabs = $teamapp->tabs()->orderBy('sort_order')->Get();
        }
        $sites = Site::pluck('site_name', 'id');
        
        return view('apps.edit-app')
        ->with('team_selection',$team_selection)
        ->with('team',$team)
        ->with('teamapps',$teamapps)
        ->with('teamapp', $teamapp)
        ->with('show_success', false)
        ->with('sites',$sites)
        ->with('selected_app', $appid)

        ->with('apptabs', $apptabs);
    }

    public function uploadAppIcon($teamid, $appid, Request $request)
    { 
        info('saving an app image image. ');

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120', // 5MB = 5120KB
        ]);

        $input = $request->all(); 

        if($request->hasfile('image'))
        {
            $file = $request->file('image');
            $imageName=time().$file->getClientOriginalName();
            $filePath = config('constants.CLOUDFRONT_ASSET_URL') . config('constants.APP_LOGO_PATH') .'logos-'.$teamid.'/'. $imageName;
            Storage::disk('s3')->put($filePath, file_get_contents($file));
            $app = Apps::where('id',$appid)->first();
            $app->appicon = $filePath;
            $app->save();
        return back()->with('success','The image has been uploaded')->with('user',$app);
        }   
    }
    
      /**
     * Set the app that will be used when this user 
     * logs in on a mobile device
     *
     * @return \Illuminate\Http\Response
     */
    public function activateApp($teamid, $appid)
    {
        $user = Auth::user(); 
        $result = UserActiveApp::processUpdates($user->id, $appid);

        return redirect()->route('apps.show', ['teamid' => $teamid]);
    }

    /**
     * Show the tab edit form 
     *
     * @return \Illuminate\Http\Response
     */
    public function editTab($teamid, $appid, $tabid)
    {
        return $this->getEditTab($teamid, $appid, $tabid);
    }

    public function addTab($teamid, $appid)
    {
        return $this->getEditTab($teamid, $appid, 'new');
    }
  
    public function deleteTab($teamid,$appid, $tabid)
    {
        $tab = Tabs::findOrFail($tabid);
        if ($tab)
        {
            $tab->delete();
            return redirect()->back()
            ->with('show_success', true);
        }
        return redirect()->back()
        ->with('show_success', false);
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function deleteApp($teamid, $appid)
    {
        Log::info('delete id: '.$appid);
        Apps::find($appid)->delete();
        session()->flash('message', 'App Deleted Successfully.');
        return redirect()->back()
        ->with('show_success', true);
    }
 

    private function getEditTab($teamid, $appid, $tabid)
    {
        $user = Auth::user(); 
        $team = Team::where('id',$teamid)->first();
        $teamapps = $team->apps;     
        $teamapp = $teamapps->where('id',$appid)->first();

        // Handle blank/new app context (appid = 0 or missing)
        if ($appid == 0 || $teamapp === null) {
            $teamapp = Apps::getBlankApp($user);
            $teamapp->id = 0;
            $teamapp->team_id = (int) $teamid;
            $team_tabs = collect();
        } else {
            $team_tabs = $teamapp->tabs;
        }

        $index=1;
        $sort_orders = [$index];
        
        // Log::info('in getEditTab, $tabid: '.$tabid);
        if ($tabid == 'new')
        {
            $tab_data = Tabs::make();
            $tab_data->id=$tabid ;
            $tab_data->app_id = $appid;  
            $tab_data->sort_order = $index; 
            $tab_data->parent = 0; 
        }
        else
        {
            $tab_data = $team_tabs->where('id',$tabid)->first();
        }
        
        $hasMore = false;
        $moreindex = 0;
        foreach($team_tabs as $tab)
        {
            $sort_orders[] = $index;
            if ($tab->page_url == config('constants.MORE_TAB'))
            {
                $hasMore = true;
                $moreindex = $index-1;
            }

            $index = $index + 1;
        }
        
        //for the last overflow tab, called More
        if ( $hasMore )
        {
            $tabsIndexed = $team_tabs->values();
            $moreTab = $tabsIndexed->get($moreindex);
            $more = [[0,'Not on More'],
                [$moreTab->id,$moreTab->label]];
        }
        else
        {
            $more = [[0,'Not on More or Is More']];
        }
        $icon_data = FlutterIcons::pluck('icon_name','id');

        return view('apps.edit-tab')
        ->with('selected_app',$appid)
        ->with('tabdata', $tab_data)
        ->with('moredata', $more)
        ->with('icondata', $icon_data)
        ->with('sortorders', $sort_orders);
    }

    /**
     * Show the sync pages to app form
     *
     * @return \Illuminate\Http\Response
     */
    public function syncPagesToApp($teamid, $appid, $siteid = null)
    {
        $user = Auth::user();
        // Authorize by membership/ownership
        if (!$user || !$user->isTeamMemberOrOwner((int) $teamid)) {
            return $this->sendError('Unauthorized.', ['error' => 'You do not have access to this team.'], 403);
        }

        $team = Team::where('id', $teamid)->first();
        $teamapps = $team->apps;
        $teamapp = $teamapps->where('id', $appid)->first();

        if (!$teamapp) {
            abort(404, 'App not found');
        }

        // Use provided site_id or fall back to app's associated site
        $site = null;
        if ($siteid) {
            $site = Site::find($siteid);
        } else {
            $site = $teamapp->site;
        }

        if (!$site) {
            return view('apps.sync-pages-to-app')
                ->with('team', $team)
                ->with('teamapp', $teamapp)
                ->with('sites', collect())
                ->with('sitePages', collect())
                ->with('error', 'This app is not associated with a site. Please select a site in the app settings first.');
        }

        $sites = Site::pluck('site_name', 'id');
        $sitePages = $site->sitePages;

        return view('apps.sync-pages-to-app')
            ->with('team', $team)
            ->with('teamapp', $teamapp)
            ->with('sites', $sites)
            ->with('sitePages', $sitePages)
            ->with('selectedSiteId', $site->id);
    }

    /**
     * Handle the sync pages to app submission
     *
     * @return \Illuminate\Http\Response
     */
    public function syncPagesToAppSubmit(Request $request, $teamid, $appid)
    {
        $user = Auth::user();
        // Authorize by membership/ownership
        if (!$user || !$user->isTeamMemberOrOwner((int) $teamid)) {
            return $this->sendError('Unauthorized.', ['error' => 'You do not have access to this team.'], 403);
        }

        $team = Team::where('id', $teamid)->first();
        $teamapps = $team->apps;
        $teamapp = $teamapps->where('id', $appid)->first();

        if (!$teamapp) {
            abort(404, 'App not found');
        }

        // Get site from request or fall back to app's site
        $siteId = $request->input('site_id');
        $site = $siteId ? Site::find($siteId) : $teamapp->site;
        
        if (!$site) {
            return redirect()->route('apps.edit', ['teamid' => $teamid, 'appid' => $appid])
                ->with('error', 'App is not associated with a site.');
        }

        $selectedPages = $request->input('selected_pages', []);

        if (empty($selectedPages)) {
            return redirect()->route('apps.sync-pages', ['teamid' => $teamid, 'appid' => $appid])
                ->with('error', 'Please select at least one page to sync.');
        }

        try {
            $appSyncService = new \App\Services\AppSyncService();
            $appSyncService->syncSelectedSitePagesToApp($site, $teamapp, $selectedPages);

            return redirect()->route('apps.edit', ['teamid' => $teamid, 'appid' => $appid])
                ->with('success', 'Pages synced successfully! ' . count($selectedPages) . ' page(s) have been converted to app tabs.');
        } catch (\Exception $e) {
            Log::error('Error syncing pages to app: ' . $e->getMessage());
            return redirect()->route('apps.sync-pages', ['teamid' => $teamid, 'appid' => $appid])
                ->with('error', 'Error syncing pages: ' . $e->getMessage());
        }
    }
  
}
