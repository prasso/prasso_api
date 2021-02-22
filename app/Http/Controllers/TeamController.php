<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Tabs;
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

        $team = $user->teams->first();
        $teams = $user->teams->toArray();
  
        $teamapps = $user->teams->first()->apps;

        return view('apps.show')
            ->with('user', $user)
            ->with('teams',$teams)
            ->with('teamapps', $teamapps)
            ->with('team', $team);
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
            $tab_data = new Tabs();
            $tab_data->app_id = $appid;
        }
        else
        {
            $tab_data = $teamapp->tabs->where('id',$tabid)->first();
        }

        $index=1;
        $sort_orders = [$index];
        foreach($teamapp->tabs as $tab)
        {
            $index = $index + 1;
            $sort_orders[] = $index;
        }
        //for the last overflow tab, called More
        if ($index > 4 )
        {
            $more = [[0,'Not on More'],
                [$teamapp->tabs[4]->id,$teamapp->tabs[4]->label]];
        }
        else
        {
            $more = [[0,'Not on More']];
        }
        $icon_data = FlutterIcons::pluck('icon_name','id');

        return view('apps.edit-tab')
        ->with('tabdata', $tab_data)
        ->with('moredata', $more)
        ->with('icondata', $icon_data)
        ->with('sortorders', $sort_orders);
    }
  
}
