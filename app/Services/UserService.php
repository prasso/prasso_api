<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\Invitation;
use App\Models\Team;
use App\Models\TeamUser;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\newsletter_signup;
use Str;

class UserService 
{
    
    public function saveUser($request)
    {
      $user_from_request =  $request->json()->all();

      $user_access_token = $user_from_request['appToken'];
      $user = User::getUserByAccessToken($user_access_token);
      if (isset($user))
      {
          // can't use this $user->fill($user_from_request); - reason is because some of the json tags don't match
          $user->fillFromAppjson($user_from_request);

        Log::info('user to be saved: ' . json_encode($user));
          
          $updatedUser = $user->save();

    Log::info('updated user: ' . json_encode($user));
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

        TeamUser::removeTeamMembership($email, config('constants.NEWSLETTER_TEAM_ID'));
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

    public function register($user,$role, $sendInvitation)
    {
        if (!isset($role))
        {
            $role=config('constants.TEAM_USER_ROLE');
        }
        if ($role == config('constants.NEWSLETTER_ROLE_TEXT'))
        {
            //add them to team 2 (newsletter team) if signing up for the newsletter;
            TeamUser::addToTeam($user,config('constants.NEWSLETTER_TEAM_ID')); 
        }
        else
        {
          $user->ownedTeams()->save(Team::forceCreate([
              'user_id' => $user->id,
              'name' => explode(' ', $user->name, 2)[0]."'s Team",
              'personal_team' => true,
          ]));
          $user->refresh();
          TeamUser::addToTeam($user,$user->allTeams()->first()->id); 
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
                //we are done if our guys are newsletter
                return;
            }
        }
        else
        {
          $user->sendWelcomeEmail();
          TeamUser::addToBaseTeam($user);     
        }
       
        $success['status'] = 'logged in';  //the job will obtain one for future use
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
            Log::info('buildConfigReturn no access token: '.json_encode($user));
            // Revoke all tokens, we are getting a fresh one
            if ($user->tokens())
            {
                $user->tokens()->delete();
            }
            $user_access_token = $user->createToken(config('app.name'))->accessToken->token;
            $success['token'] = $user_access_token; 
            //Log::info('AuthController::buildConfigReturn - Refreshed User Access Token');
        }
        else
        {
            $success['token'] = $user_access_token; 
        }
        
        $success['name'] = $user->name;
        $success['uid'] = $user->firebase_uid;
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
          $success['personal_team_id'] = $user->teams[0]->id;
          $success['team_coach_id'] = $user->teams[0]->user_id;
         
        }
        else
        {
          $success['personal_team_id'] = $user->current_team_id;
          $success['team_coach_id'] = Team::where('id',$user->current_team_id)->first()->user_id;
        }

        if ($user->isInstructor())
        { 
          try{
              $success['team_members'] = json_encode(Instructor::getTeamMembersFor($user->teams[0]->id));
            } catch (\Throwable $e) {
              Log::info($e);
              $success['timeZone'] = $user->timeZone;
              $success['team_members'] = [];
            }
          }
        else
        {
          $success['team_members'] = [];
        }
        
        $app_data = $appsService->getAppSettingsBySite($site, $user,$user_access_token);
        
        $success['app_data'] = $app_data; //configuration for setting up the app is here

    Log::info('app data being returned: ' . json_encode($success)); 
        return $success;
    }

}





