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
    public function index()
    {
        $user = Auth::user(); 

        $activeApp = UserActiveApp::where('user_id',$user['id'])->first();
  
        $team = Team::where('id',$user->current_team_id)->first();
     
        $teams = $user->teams->toArray();
  
        $teamapps = $team->apps;
        
        $activeAppId = '0';
        if (isset($activeApp->app_id))
        {
            $activeAppId = $activeApp->app_id;
        }

        return view('apps.show')
            ->with('user', $user)
            ->with('teams',$teams)
            ->with('teamapps', $teamapps)
            ->with('team', $team)
            ->with('activeappid',$activeAppId);
    }

    
    public function editTeam($teamid)
    {
        $user = Auth::user(); 
        if ($user->current_team_id != $teamid)
        {
            $response['message'] = trans('messages.invalid_token');
            $response['success'] = false;
            $response['status_code'] = \Symfony\Component\HttpFoundation\Response::HTTP_UNAUTHORIZED;
            return $this->sendError('Unauthorized.', ['error' => 'Please login again.'], 400);
        }
        $team = $user->currentTeam;
        
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
        $user_access_token = isset($user->personalAccessToken) ? $user->personalAccessToken->token : null;

        $team = $user->teams->where('id', $teamid)->first();

        if (count($team->users)>0)
        {
            $recipients = $team->users->sortBy('name');
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
        //Log::info('In processTeamMessages');
        $user = Auth::user();

        // MAKE THIS WORK TO EITHER SEND PUSH NOTIFICATIONS OR AN EMAIL
        // OR A TXT MESSAGE IF THE USER'S PROFILE HAS A PHONE NUMBER
    
        $is_email_request=false;
        $is_pn_request=false;
        
        $input = $request->all();
        $sendto = [];
        $notify = new Notifications();
        $notify->user_sender = $user->id;
        if (isset($input['subject']) )
        {
            $is_pn_request = true;
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
        $notify->action='openapp';

        foreach($input as $formitem)
        {
            if (str_starts_with($formitem, 'member-')  )
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
            else if ($is_email_request)
            {
                //ship this off to the logic that processes emails
                // TODO TODO TODO
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
        $team = $user->teams->where('id',$teamid)->first();
        $teamapps = $team->apps;     
        $teamapp = $teamapps->where('id',$appid)->first();
        $team_selection = $user->teams->pluck('name','id');
        if ($appid == 0 || $teamapp ==  null)
        {
            $teamapp = $appsService->getBlankApp($user);
        }
        $apptabs = $teamapp->tabs()->orderBy('sort_order')->Get();
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
  
    public function deleteTab($appid, $tabid)
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
    public function deleteApp($id)
    {
        Apps::find($id)->delete();
        session()->flash('message', 'App Deleted Successfully.');
        return redirect()->back()
        ->with('show_success', true);
    }
 

    private function getEditTab($teamid, $appid, $tabid)
    {
        $user = Auth::user(); 
        $team = $user->teams->where('id',$teamid)->first();
        $teamapps = $team->apps;     
        $teamapp = $teamapps->where('id',$appid)->first();

        $index=1;
        $sort_orders = [$index];
        
        // Log::info('in getEditTab, $tabid: '.$tabid);
        if ($tabid == 'new')
        {
            Log::info('set up for new tab');
            $tab_data = Tabs::make();
            $tab_data->id=$tabid ;
            $tab_data->app_id = $appid;  
            $tab_data->sort_order = $index; 
            $tab_data->parent = 0; 
        }
        else
        {
            $tab_data = $teamapp->tabs->where('id',$tabid)->first();
        }
        
        $hasMore = false;
        $moreindex = 0;
        foreach($teamapp->tabs as $tab)
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
            $more = [[0,'Not on More'],
                [$teamapp->tabs[$moreindex]->id,$teamapp->tabs[$moreindex]->label]];
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
  
}
