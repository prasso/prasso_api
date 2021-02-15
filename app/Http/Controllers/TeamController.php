<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

        $apptabs = $teamapps->first()->tabs()->orderBy('sort_order')->Get();

        return view('apps.show')
            ->with('user', $user)
            ->with('teams',$teams)
            ->with('teamapps', $teamapps)
            ->with('team', $team)
            ->with('apptabs', $apptabs);
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

        return view('apps.edit-app')
        ->with('team_selection',$team_selection)
        ->with('team',$team)
        ->with('teamapps',$teamapps)
        ->with('teamapp', $teamapp)
        ->with('show_success', false);
    }

    
    /**
     * Show the tab edit form 
     *
     * @return \Illuminate\Http\Response
     */
    public function editTab($teamid, $appid, $tabid)
    {
        $user = Auth::user(); 
        $team = $user->teams->where('id',$teamid)->first();
        $teamapps = $team->apps;     
        $teamapp = $teamapps->where('id',$appid)->first();
        $tab_data = $teamapp->tabs->where('id',$tabid)->first();

        $index=1;
        $sort_orders = [$index];
        foreach($teamapp->tabs as $tab)
        {
            $index = $index + 1;
            $sort_orders[] = $index;
        }
        //for the last overflow tab, called More
        if ($index > 4)
        {
            $more = [[0,'Not on More'],
                [5,$teamapp->tabs[4]->label]];
        }
        else
        {
            $more = [[0,'Not on More']];
        }

        return view('apps.edit-tab')
        ->with('team',$team)
        ->with('sort_orders', $sort_orders )
        ->with('teamapps',$teamapps)
        ->with('teamapp', $teamapp)
        ->with('tab_data', $tab_data)
        ->with('more_data', $more)
        ->with('show_success', false);
    }
}
