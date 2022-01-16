<?php

namespace App\Services;

use App\Models\Apps;
use App\Models\Team;
use App\Models\Site;
use App\Models\User;
use App\Models\UserActiveApp;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;


class AppsService 
{

    // for apps that have no role determinations.
    //this serves the first app only app serves only one at this time ( will possibly add more later)
    public function getBarimorphosisAppSettings($user)
    {
      //  Log::info('getBarimorphosisAppSettings app id: '. config('constants.BARIMORPHOSIS_APP'));
        $app_data = Apps::with('tabs')->with('team')
            ->where('id',config('constants.BARIMORPHOSIS_APP'))
            ->first();
      //  Log::info(json_encode($app_data));
        return json_encode($app_data);
    }

     //get tabs based on the role this user plays in the app
    //(instructor will have app management features. users will not)
    public function getAppSettingsBySite(Site $site, $user,$user_access_token) 
    {
        //Log::info('user in getAppSettingsBySite: '.json_encode($user));
        $returnval='';
      
        if ( !isset($user->roles) || 
            (isset($user->roles) && count($user->roles) == 0))
        {
            //only tabs with  null roles
            $app_data = Apps::with('nullroletabs')->with('team')
            ->where('site_id', $site->id)
            ->first();

            //fix the label
            $returnval = str_replace('nullroletabs','tabs',json_encode($app_data));

        }
        else
        {
            //return all if a role is set for this user
            //do though, allow for some to be marked as only nullrole ( such as subscribepage )
            $app_data = Apps::with('instructorroletabs')
                ->with('team')
                ->where('site_id', $site->id)
                ->first();
            $returnval = str_replace('instructorroletabs','tabs',json_encode($app_data));
        }
        //update any user specific headers
        $returnval = str_replace(config('constants.USER_TOKEN'), $user_access_token, $returnval);

        if (isset($user->thirdPartyToken))
        {
            $returnval = str_replace(config('constants.THIRD_PARTY_TOKEN'), $user->thirdPartyToken->THIRD_PARTY_TOKEN, $returnval);
        }
        if (isset($user->current_team_id ))
        {
            $returnval = str_replace(config('constants.TEAM_ID'), $user->current_team_id, $returnval);
        }
        $returnval = str_replace(config('constants.CSRF_HEADER'), csrf_token(), $returnval);
 
        //Log::info('app settings by site: '.$returnval);
       return $returnval;    
    }


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
            ->where('team_id',$user->current_team_id)
            ->first();
            if ($app_data == null )
            {
                $app_data = $this->getBlankApp($user);
                $app_data->team_id=$user->teams[0]->id;
            }
        }
       
       return json_encode($app_data);
    }

    public function getBlankApp($user)
    {
        return Apps::getBlankApp($user);
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





