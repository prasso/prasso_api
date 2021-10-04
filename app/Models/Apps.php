<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


/*
    Apps have Tabs
*/
class Apps extends Model
{
    use HasFactory;
    use HasTimestamps;
    
    protected $fillable = [
        'team_id','site_id' ,'appicon', 'app_name', 'page_title', 'page_url', 'sort_order', 'user_role'
    ];

    //includes admin level tabs
    public function tabs()
    {
        return $this->hasMany(\App\Models\Tabs::class, "app_id", "id")
        ->orderBy('sort_order');
    }

    //for app users that have no admin roles added
    public function nullroletabs()
    {
        return $this->hasMany(\App\Models\Tabs::class, "app_id", "id")
            ->where("team_role", null)
            ->orderBy('sort_order');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }

    public function activeApp()
    {
        return $this->hasOne( UserActiveApp::class, 'app_id', 'id');
    }

    public static function getBlankApp(User $user)
    {
        $app0 = Apps::with('tabs')->with('team')->with('activeApp')
        ->where('team_id',1)
        ->first();

        $blankapp = Apps::copyApp($app0, $user);
        return $blankapp;
    }

    public static function copyApp(Apps $app, User $user)
    {
        $newapp = Apps::forceCreate(
            ['team_id' => $user->teams[0]->id, 
            'appicon' => $app->appicon, 
            'app_name' => $app->app_name, 
            'page_title' => $app->page_title,
            'page_url' => $app->page_url,
            'sort_order' => $app->sort_order ]
        );
        $user->refresh();

        foreach ($app->tabs as $tab)
        {
            Tabs::forceCreate(
                ['app_id' => $newapp->id]
            );
        }

        return $newapp;
    }
    public static function processUpdates( $appModel)
    {

        Apps::updateOrCreate(['id' => $appModel['id']] , 
        ['team_id' => $appModel['team_id'], 
        'appicon' => $appModel['appicon'], 
        'app_name' => $appModel['app_name'], 
        'page_title' => $appModel['page_title'],
        'page_url' => $appModel['page_url'],
        'sort_order' => $appModel['sort_order'] ] );

    }

}
