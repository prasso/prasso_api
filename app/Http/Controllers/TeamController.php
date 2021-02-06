<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $apptabs = $teamapps->first()->tabs()->Get();

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
        $team = $user->teams->where('id',$teamid);
        $teamapps = $team->apps();
        dd($teamapps);
        $teamapp = $team->apps->pluck($appid);
        
        return view('apps.edit-app')
        ->with('team',$team)
        ->with('teamapp', $teamapp);
    }

}
