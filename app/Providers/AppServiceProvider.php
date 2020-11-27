<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use App\Models\Apps;

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

    /*
    a method to return the setup for this person's application
    */
    public static function getAppSettings($apptoken)
    {
        $app = DB::table('personal_access_tokens')->where('token', '=', $apptoken)->first();
            
        if ($app == null)
        {
            //bad access token, return 404
            abort(404);
        }

        $app_data = Apps::with('tabs')
            ->where('token_id',$app->id)
            ->get();

       return json_encode($app_data);
    }
}
