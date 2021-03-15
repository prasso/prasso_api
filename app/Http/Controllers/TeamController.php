<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Tabs;
use App\Models\UserActiveApp;
use App\Models\FlutterIcons;

class TeamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
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
  
        $team = $user->teams->first();
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

    /**
     * Show the app edit form 
     *
     * @return \Illuminate\Http\Response
     */
    public function editApp($teamid, $appid)
    {
        $user = Auth::user(); 
        $team = $user->teams->where('id',$teamid)->first();
        $teamapps = $team->apps;     
        $teamapp = $teamapps->where('id',$appid)->first();
        $team_selection = $user->teams->pluck('name','id');

        $apptabs = $teamapp->tabs()->orderBy('sort_order')->Get();

        return view('apps.edit-app')
        ->with('team_selection',$team_selection)
        ->with('team',$team)
        ->with('teamapps',$teamapps)
        ->with('teamapp', $teamapp)
        ->with('show_success', false)
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
        return $this->getEditTab($teamid, $appid, 0);
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

 
    private function getEditTab($teamid, $appid, $tabid)
    {
        $user = Auth::user(); 
        $team = $user->teams->where('id',$teamid)->first();
        $teamapps = $team->apps;     
        $teamapp = $teamapps->where('id',$appid)->first();
        
        if ($tabid == 0)
        {
            $tab_data = Tabs::make();
            $tab_data->app_id = $appid;  
        }
        else
        {
            $tab_data = $teamapp->tabs->where('id',$tabid)->first();
        }

        $index=1;
        $sort_orders = [$index];
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
        ->with('tabdata', $tab_data)
        ->with('moredata', $more)
        ->with('icondata', $icon_data)
        ->with('sortorders', $sort_orders);
    }
  
}
