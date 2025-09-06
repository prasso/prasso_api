<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use App\Services\UserService;
use App\Models\Instructor;
use Auth;

class MySiteController extends BaseController
{

    protected $userService;

    public function __construct(Request $request,UserService $userServ)
    {
        parent::__construct( $request);
        $this->middleware('instructorusergroup');

        $this->userService = $userServ;
    }


 

    /**
     * Show the form for editing the logged in user's site.
     * check first these things:
     *   does the current user have access to this site?
     *      a user can edit a site if they belong to the sites team and have instructor permissions
     *      livestreams are a paid feature and can be added to the site if the site has a paid subscription
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function editMySite(Request $request)
    {
        // the way this is written, there's no straightforward method of debugging
        // with localhost when the site is not setup in sites as localhost
        // so, set this up in the site->hosts field for the user's site
        if (!Controller::userOkToViewPageByHost($this->userService))
        {
            return redirect('/login');
        }
        $mysite = Controller::getClientFromHost();
        
        // Ensure site data is fully loaded with relationships
        $mysite = Site::with(['teams', 'livestream_settings', 'stripe'])->find($mysite->id);
        
        $user = Auth::user();
        if (!$user->isInstructor() && !$user->isTeamOwnerForSite($mysite))
        {
            
            abort(403, 'Unauthorized action.');
            
        }
        
        $team = $mysite->teams()->first();
        $team_selection = $team->pluck('name','id');
        return view('sites.my-site-editor')
            ->with('site', $mysite)
            ->with('user', $user)
            ->with('team', $user->currentTeam)
            ->with('team_selection', $team_selection);

    }   



    public function editSite($siteid)
    {
        $user = Auth::user();
        $site = Site::where('id',$siteid)->with('teams')->first();
        
        $team = $site->teams()->first();
        if (!$user->canManageTeamForSite($team->id))
        {
            abort(403, 'Unauthorized action.');
        }
        

        $team_selection = $team->pluck('name','id');
        return  view('sites.my-site-editor')    
            ->with('site', $site)
            ->with('user', $user)
            ->with('team', $team)
            ->with('team_selection', $team_selection);
    }

}
