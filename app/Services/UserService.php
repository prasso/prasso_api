<?php

namespace App\Services;

use App\Models\CommunityAccessTokens;
use Illuminate\Support\Facades\Log;
use App\Models\Invitation;
use App\Models\Team;
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
      $team = $site->teams->first();
      if ($team->user_id == $user->id)
      {
        //the user is the owner of the team, so they are on the team and as owner they have access to edit 
        return true;
      }
      // Build the query
      $query = TeamUser::where('user_id', $user->id)->where('team_id', $team->id);

      // // if need to troubleshoot user login: Get the SQL query with placeholders
      // $sql = $query->toSql();
      // Log::info($sql);
      $teamuser = $query->first();
      if ($teamuser != null)
      {
        return true;
      }
      return false;
    }

    // create the instructor access and return success
    public function addOrUpdateSubscription($request, $user, $appsService, $site)
    {
      $inputs =  $request->json()->all();
info('addOrUpdateSubscription: '.json_encode($user));
        //if subscribed, add the instructor role to user_roles if this user doesn't have it
        $instructoruser = $this->instruc->fetchUserByCredentials($user->email);
        if ($instructoruser == null)
        {
          $this->instruc->setupAsInstructor($user);
        }
        $success = $this->buildConfigReturn($user, $appsService, $site);
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

    public function subscribeNewsletter($email)
    {
      $usr = User::where('email',$email)->first();
      if ($usr == null)
      {
        //add the user
        $keeper_password = Str::random(10);
        $bcrypt_password = bcrypt($keeper_password);
        
        $user = new User();
        $user->name = $email;
        $user->email = $email;
        $user->password = $bcrypt_password;
        $user->firebase_uid = $bcrypt_password; //how will we get this straight later when they log in from the app
        $user->save();
      }
      $this->register($user,config('constants.NEWSLETTER_ROLE_TEXT'), true); //this leverages the invitation feature to get email confirmed for the newsletter
   
    }

    public function subscribeInstructor($json)
    {
      $usr = User::where('email',$json->data->object->email)->first();
      if ($usr != null)
      {//add the role
        //store the subscription so we can checkit
        $usr->addRole(config('constants.INSTRUCTOR_ROLE_TEXT'));
      }
      else
      {
        //add the user
        $keeper_password = Str::random(10);
        $bcrypt_password = bcrypt($keeper_password);
        
        $user = new User();
        $user->name = $json->data->object->name;
        $user->email = $json->data->object->email;
        $user->password = $bcrypt_password;
        $user->firebase_uid = $bcrypt_password; //how will we get this straight later when they log in from the app
        $user->save();
        $this->register($user,config('constants.INSTRUCTOR_ROLE_TEXT'), true);
      }
    }

    protected function register($user,$role, $sendInvitation)
    {
      $site_team_id = -1;
        if (!isset($role))
        {
            $role=config('constants.TEAM_USER_ROLE');
        }
        if ($role == config('constants.NEWSLETTER_ROLE_TEXT'))
        {
            //add them to team 2 (newsletter team) if signing up for the newsletter;
            TeamUser::addToTeam($user,config('constants.NEWSLETTER_TEAM_ID')); 
            $site_team_id = config('constants.NEWSLETTER_TEAM_ID');
        }
        else
        {
          $new_team = $user->ownedTeams()->save(Team::forceCreate([
              'user_id' => $user->id,
              'name' => explode(' ', $user->name, 2)[0]."'s Team",
              'personal_team' => true,
              'phone' => $user->phone,
          ]));
          $user->refresh();
          TeamUser::addToTeam($user,$new_team->id); 
        }
        $user->save();

        if ($sendInvitation)
        {
            $invitation = Invitation::create([
              'user_id' => $user->id,

              'team_id' => $user->current_team_id,

              'role' => $role,
              'email' => $user->email,
            ]);
            $invitation->sendEmailInviteNotification();
            if ($role == config('constants.NEWSLETTER_ROLE_TEXT'))
            {
                //no community, no third party. we are done if our guys are newsletter
                return;
            }
        }
        else
        {
          $user->sendWelcomeEmail($site_team_id);
          TeamUser::addToBaseTeam($user);     
        }
       
      //  $this->makeCommunityUser($user);

       // ObtainThirdPartyToken::dispatch($user);
        $success[config('constants.thirdPartyToken')] = 'initializing';  //the job will obtain one for future use
        return $success;
    }

    // TODO site subteams_enabled requires a modification here but I don't have full concept of how it works yet
    // will potentially need 'my_table.subteam_id', $subteamIds where subteamIds is an array of team ids the user belongs to
    public function registerForSite($user, $site, $role, $sendInvitation)
    {
        if (!isset($role))
        {
            $role=config('constants.TEAM_USER_ROLE');
        }
        //get the team from the site
        if ($site->supports_registration) {
          $team = $site->teamFromSite();
          $team->users()->attach(
              $user,
              ['role' => 'user']
          );
          $user->current_team_id = $team->id;
          $user->save();
          \Laravel\Jetstream\Events\TeamMemberAdded::dispatch($team, $user);
        }
        else{

          $team = $user->ownedTeams()->forceCreate([
              'user_id' => $user->id,
              'name' => explode(' ', $user->name, 2)[0] . "'s Team",
              'personal_team' => true,
              'phone' => $user->phone,
          ]);
          
          $user->currentTeam()->associate($team);
          $user->save();
        }
        $user->refresh();
        if ($team == null)
        {
            $team = $user->currentTeam;
        }
        TeamUser::addToTeam($user,$team->id); 
        $site_team_id = $team->id;
        $user->save();

        if ($sendInvitation)
        {
            $invitation = Invitation::create([
              'user_id' => $user->id,

              'team_id' => $user->current_team_id,

              'role' => $role,
              'email' => $user->email,
            ]);
            $invitation->sendEmailInviteNotification();
            if ($role == config('constants.NEWSLETTER_ROLE_TEXT'))
            {
                //no community, no third party. we are done if our guys are newsletter
                return;
            }
        }
        else
        {
          $user->sendWelcomeEmail($site_team_id);
          $assign_team_id = false;
          TeamUser::addToBaseTeam($user, $assign_team_id);     
        }
       
      //  $this->makeCommunityUser($user);

       // ObtainThirdPartyToken::dispatch($user);
        $success[config('constants.thirdPartyToken')] = 'initializing';  //the job will obtain one for future use
        return $success;
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





