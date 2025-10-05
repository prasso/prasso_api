<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use App\Services\UserService;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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
        
        // If this is the Prasso site (ID 1), only super admins should be able to edit it
        if ($mysite->id == 1) {
            // Get user roles directly from the database
            $userRoles = \App\Models\UserRole::where('user_id', $user->id)->get();
            
            // Check if user is a super admin
            $isSuperAdmin = false;
            foreach ($userRoles as $userRole) {
                if ($userRole->role_id == config('constants.SUPER_ADMIN')) {
                    $isSuperAdmin = true;
                    break;
                }
            }
            
            if (!$isSuperAdmin) {
                abort(403, 'Only super admins can edit the Prasso site.');
            }
        } else {
            // For other sites, check if the user is a team owner for this specific site
            $teamFromSite = $mysite->teams()->first();
            if (!$teamFromSite) {
                abort(404, 'No team found for this site.');
            }
            
            // Get user roles directly from the database
            $userRoles = \App\Models\UserRole::where('user_id', $user->id)->get();
            
            // Check if user is a super admin
            $isSuperAdmin = false;
            foreach ($userRoles as $userRole) {
                if ($userRole->role_id == config('constants.SUPER_ADMIN')) {
                    $isSuperAdmin = true;
                    break;
                }
            }
            
            // Check if user is the team owner
            $isTeamOwner = $user->id == $teamFromSite->user_id;

            // Check if the user has the instructor role for this site's team
            $isSiteInstructor = \App\Models\TeamUser::where('team_id', $teamFromSite->id)
                ->where('user_id', $user->id)
                ->where('role', 'instructor')
                ->exists();
            
            if (!$isSuperAdmin && !$isTeamOwner && !$isSiteInstructor) {
                abort(403, 'Unauthorized action. You must be a site admin for this specific site.');
            }
        }
        
        $team = $mysite->teams()->first();
        // Build team selection from the site's teams (not the user's teams)
        $team_selection = $mysite->teams->pluck('name','id');
        return view('sites.my-site-editor')
            ->with('site', $mysite)
            ->with('user', $user)
            // Use the site's team so downstream links (e.g., Edit Mobile App) target this Site's apps
            ->with('team', $team)
            ->with('team_selection', $team_selection);

    }   



    public function editSite($siteid)
    {
        $user = Auth::user();
        $site = Site::where('id',$siteid)->with('teams')->first();
        
        // If this is the Prasso site (ID 1), only super admins should be able to edit it
        if ($site->id == 1) {
            // Get user roles directly from the database
            $userRoles = \App\Models\UserRole::where('user_id', $user->id)->get();
            
            // Check if user is a super admin
            $isSuperAdmin = false;
            foreach ($userRoles as $userRole) {
                if ($userRole->role_id == config('constants.SUPER_ADMIN')) {
                    $isSuperAdmin = true;
                    break;
                }
            }
            
            if (!$isSuperAdmin) {
                abort(403, 'Only super admins can edit the Prasso site.');
            }
        } else {
            $team = $site->teams()->first();
            if (!$team) {
                abort(404, 'No team found for this site.');
            }
            
            // Get user roles directly from the database
            $userRoles = \App\Models\UserRole::where('user_id', $user->id)->get();
            
            // Check if user is a super admin
            $isSuperAdmin = false;
            foreach ($userRoles as $userRole) {
                if ($userRole->role_id == config('constants.SUPER_ADMIN')) {
                    $isSuperAdmin = true;
                    break;
                }
            }
            
            // Check if user is the team owner
            $isTeamOwner = $user->id == $team->user_id;
            
            if (!$isSuperAdmin && !$isTeamOwner) {
                abort(403, 'Unauthorized action. You must be a site admin for this specific site.');
            }
        }
        
        $team = $site->teams()->first();
        // Build team selection from the site's teams
        $team_selection = $site->teams->pluck('name','id');
        return  view('sites.my-site-editor')    
            ->with('site', $site)
            ->with('user', $user)
            ->with('team', $team)
            ->with('team_selection', $team_selection);
    }

}
