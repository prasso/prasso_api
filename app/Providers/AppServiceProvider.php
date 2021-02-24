<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use App\Models\Apps;
use App\Models\Team;
use App\Models\User;
use App\Models\UserActiveApp;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /// user in team (user_id ) team in app (team_id) app in tabs (app-id)
    /// a method to return the setup for this person's application
    public static function getAppSettingsByUser($user)
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
           $app_data = Apps::with('tabs')->with('team')->with('activeApp')
            ->where('team_id',$user->teams[0]->id)
            ->first();
        }
       
       return json_encode($app_data);
    }
    /*
    a method to return the setup for an app by app token
    */
    public static function getAppSettings($apptoken)
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

    public static function saveApp($request)
    {
        $app = Apps::create($request->all());
        return json_encode($app);
    }
}
