<?php

namespace App\Services;

use App\Models\CommunityAccessTokens;
use Illuminate\Support\Facades\Log;
use App\Models\Invitation;
use App\Models\Team;
use App\Models\Site;
use App\Models\TeamUser;
use App\Models\TeamSite;
use App\Models\CommunityUser;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\newsletter_signup;
use Str;
use App\Http\Controllers\Controller;


class UserService 
{
  private $instruc;

    public function __construct(Instructor $suser)
    {
      $this->instruc = $suser;
    }

    public function UpdateSitesMember($user, $team, $id_of_selected_site){

        // Find the TeamSite model with the specified site ID and eager load the associated team.
        $teamSite = null;
        if (isset($team)){
          $teamSite = TeamSite::where('site_id', $id_of_selected_site)->where('team_id',$team->id)->with('team')->first();
        }
        else{
          $teamSite = TeamSite::where('site_id', $id_of_selected_site)->with('team')->first();
        }

        // If the TeamSite model doesn't exist, create a new one.
        if ($teamSite == null) {
            $teamSite = new TeamSite();
            $teamSite->site_id = $id_of_selected_site;
        }

        // If the team ID field of the TeamSite model is null, create a new team.
        if ($teamSite->team_id == null) {
          if (!isset($team))
          { 
            $team = new Team();
            $team->user_id = $user->id;
            $team->name = Site::find($id_of_selected_site)->site_name;
            $team->personal_team = false;
            $team->phone = ' ';
            $team->save();
          }

        }

        $teamSite->team_id = $team->id;
        $teamSite->save();

        // Create a new team member for the team.
        $teamSite->team->team_members()->create([
            'user_id' => $user->id,
            'role' => config('constants.TEAM_USER_ROLE'),
        ]);

    }

    /**
     *       special rules for site access if it's the base, prasso.io site
     *       1. users that are registered through prasso.io can log in there, 
     *          even if they have created another site
     */
    public function isUserOnTeam($user)
    {
      //if the user is super admin he can log into any site ( that's me for now)
      if ($user->isSuperAdmin())
      {
        return true;
      }

      //the url used in the request determines the Site
      $site = Controller::getClientFromHost();
      if ($site == null)
      {
        return false;
      }

      //Sites have teams ( may be one or may be many ) and users are attached to teams
      // that determines if the user is a member of the site
      foreach ($site->teams as $team) {
        // Check if the user is the owner of the team
        if ($team->user_id == $user->id) {
            // The user is the owner of the team, so they have access to edit
            return true;
        }

        // Build the query to check if the user is a member of the team
        $isMember = TeamUser::where('user_id', $user->id)
                            ->where('team_id', $team->id)
                            ->exists();

        if ($isMember) {
            // The user is a member of one of the teams, grant access
            return true;
        }
      }

      // If no matching team found, deny access
      return false;

    }

    // create the instructor access and return success
    public function addOrUpdateSubscription($request, $user, $appsService, $site)
    {
        $inputs = $request->json()->all();
        info('addOrUpdateSubscription: ' . json_encode($user));

        // If subscribed, add the instructor role to user_roles if this user doesn't have it
        $instructoruser = $this->instruc->fetchUserByCredentials($user->email);
        
        if ($instructoruser == null) {
            $this->instruc->setupAsInstructor($user);
        }

        // Initialize the success response
        $success = [];

        // Conditionally call buildConfigReturn if $site is not null
        if ($site) {
            $success = $this->buildConfigReturn($user, $appsService, $site);
        }

        // Set the success status regardless of the $site
        $success['status'] = 'success'; 

        return $success;
    }



    public function saveUser($request)
    {
      $user_from_request =  $request->json()->all();

      $user_access_token = $user_from_request['appToken'];
      $user = User::getUserByAccessToken($user_access_token);
      if (isset($user))
      {
          // can't use this $user->fill($user_from_request); - reason is because some of the json tags don't match
          $user->fillFromAppjson($user_from_request);

          
          $updatedUser = $user->save();

          //put the updated user into the session
          \Auth::login($user);
        }
        else
        {
          Log::info('user WAS NOT FOUND: ' . $user_from_request);
        }
       return $user;
    }

    
    /**
     * newsletters can be sent to those users with the newsletter role that have a email_verified_at date in their user record
     */
    public function confirmNewsletter($email)
    {
      $usr = User::where('email',$email)->first();
      if ($usr != null)
      {
        $usr->email_verified_at = date("Y-m-d H:i:s") ;
        $usr->save();

      try{
        $emailbcc = 'info@prasso.io'; //because .env setting is not being read on prod server!
       
        Mail::to($emailbcc,'Newsletter Subscriber')->send(new newsletter_signup($usr));
        }
        catch(\Throwable $err)
        {
            Log::info('error sending coach email: '.$err);
        }
      }

    }

    public function unsubscribe($email)
    {
      $usr = User::where('email',$email)->first();
      if ($usr != null)
      {
        $usr->email_verified_at = null ;
        $usr->save();

        TeamUser::removeTeamMembership($usr, config('constants.NEWSLETTER_TEAM_ID'));
      }

    }

    


    /**
     * Consolidate code used in multiple places
     */
    public function buildConfigReturn($user, $appsService, $site) 
    {
        $user_access_token = isset($user->personalAccessToken)? $user->personalAccessToken->token : null;

        $success = [];
        if (!isset($user_access_token))
        {
            // Revoke all tokens, we are getting a fresh one
            if ($user->tokens())
            {
                $user->tokens()->delete();
            }
            $user_access_token = $user->createToken(config('app.name'))->accessToken->token;
            $success['token'] = $user_access_token; 
        }
        else
        {
            $success['token'] = $user_access_token; 
        }
        
      //  $this->updateCommunityToken($user, $user_access_token);;

        $success['name'] = $user->name;
        $success['uid'] = $user->firebase_uid;
        $success[config('constants.thirdPartyToken')] = $this->getThirdPartyToken($user);
        $success['email'] = $user->email;
        $success['photoURL'] = $user->getProfilePhoto();
        try{
          $success['roles'] = json_encode($user->roles->makeHidden(['deleted_at', 'created_at','updated_at']));
        } catch (\Throwable $e) {
          Log::info($e);
          $success['roles'] = [];
        }
        if ($user->current_team_id == null )
        {
          //currently is first owned team
          $user->current_team_id = $user->team_owner[0]->id;
          $success['personal_team_id'] = $user->team_owner[0]->id;
          $success['team_coach_id'] = $user->team_owner[0]->user_id;
          $success['coach_uid'] = $user->getCoachUid();
        }
        else
        {
          $success['personal_team_id'] = $user->current_team_id;
          $success['team_coach_id'] = Team::where('id',$user->current_team_id)->first()->user_id;
          $success['coach_uid'] = $user->getCoachUid();
        }

        $success['team_members'] = [];
        if ($user->isInstructor())
        { 
          if (count($user->teams) > 0 && isset($user->teams[0]))
          {
          try{
              $success['team_members'] = json_encode(Instructor::getTeamMembersFor($user->current_team_id));
            } catch (\Throwable $e) {
              Log::info($e);
              $success['team_members'] = [];
            }
          }
        }
        
        $app_data = $appsService->getAppSettingsBySite($site, $user,$user_access_token);
        $success['app_data'] = $app_data; //configuration for setting up the app is here

        return $success;
    }

    public function updateCommunityToken($user, $user_access_token)
    {
      return //fix this

      
      $community_token = CommunityAccessTokens::where('user_id',$user->id)->first();
      if ($community_token == null)
      {
        //make sure they are in community users first.
        $cusr = CommunityUser::where('id',$user->id)->first();
        if ($cusr == null)
        {
            $communityuser = $this->makeCommunityUser($user);
        }
        $community_token = CommunityAccessTokens::forceCreate([
            'token' => $user_access_token,
            'user_id' => $user->id,
            'last_activity_at' => date("Y-m-d H:i:s"),
            'created_at' => date("Y-m-d H:i:s"),
            'type' => 'session_remember'
        ]);
      }
      else
      {
          $community_token->token = $user_access_token;
          $community_token->last_activity_at = date("Y-m-d H:i:s");
          $community_token->created_at = date("Y-m-d H:i:s");
          $community_token->save();
      }
    }

    public function updateCommunityUser($user)
    {
      return;//todo fix

      $community_user = CommunityUser::where('id',$user->id)->first();
      if ($community_user == null)
      {
        $community_user = $this->makeCommunityUser($user);
      }
      $community_user->username = $user->name?str_replace($user->name,' ',''):'JustAUser '.$user->id;
      $community_user->email = $user->email;
      $community_user->save();

    }

    private function makeCommunityUser($user)
    {
return; //todo fix
      $communityuser = CommunityUser::forceCreate([
        'id' => $user->id,
        'username' => 'JustAUser '.$user->id,
        'email' => $user->email,
        'password' => $user->password,
        'is_email_confirmed' => '1',
        'joined_at' => $user->created_at,
        'last_seen_at' => $user->created_at
    ]);
    return $communityuser;
    }

    public function getThirdPartyToken(User $user)
    {
      $yh_token = '';
      
      return $yh_token; 
    }
}





