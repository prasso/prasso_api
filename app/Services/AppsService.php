<?php

namespace App\Services;

use App\Models\Apps;
use App\Models\Team;
use App\Models\User;
use App\Models\UserActiveApp;
use Illuminate\Support\Facades\Log;


class AppsService 
{

    /// user in team (user_id ) team in app (team_id) app in tabs (app-id)
    /// a method to return the setup for this person's application
    public function getAppSettingsByUser(User $user)
    {
        //which app has the user selected out of the team's apps to be used on  login
        $activeApp = UserActiveApp::where('user_id',$user->id)->first();
        if (isset($activeApp->app_id))
        {
            $app_data = Apps::with('tabs')->with('team')->with('activeApp')
            ->where('id',$activeApp->app_id)
            ->first();
        }
        else
        { 
           if ( count($user->teams)<1 )
           {
               //add a team for this user. it didn't happen when registered. maybe an early user
               $user->ownedTeams()->save(Team::forceCreate([
                'user_id' => $user->id,
                'name' => explode(' ', $user->name, 2)[0]."'s Team",
                'personal_team' => true,
            ]));
            $user = $user->fresh();
           }
           $app_data = Apps::with('tabs')->with('team')->with('activeApp')
            ->where('team_id',$user->teams[0]->id)
            ->first();
            if ($app_data == null )
            {
                $app_data = $this->getBlankApp();
                $app_data->team_id=$user->teams[0]->id;
            }

        }
       
       return json_encode($app_data);
    }

    public function getBlankApp()
    {
        return Apps::with('tabs')->with('team')->with('activeApp')
        ->where('team_id',0)
        ->first();
    }

    /*
    a method to return the setup for an app by app token
    */
    public function getAppSettings(string $apptoken)
    {   
        $user = User::select('users.*','users.firebase_uid AS uid')
        ->join('personal_access_tokens', 'users.id', '=', 'personal_access_tokens.tokenable_id')
        ->where('personal_access_tokens.token', '=', $apptoken)
        ->first();

        if ($user == null)
        {
            return '';
        }

        $app_data = Apps::with('tabs')
            ->where('team_id',$user->teams[0]->id)
            ->get();

            
       return json_encode($app_data);
    }

    public function saveApp($request)
    {
        $app = Apps::create($request->all());
        return json_encode($app);
    }

}





