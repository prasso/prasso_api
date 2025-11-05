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
        'team_id','site_id' ,'appicon', 'app_name', 'page_title', 'page_url', 'pwa_app_url', 'pwa_server_url', 'sort_order', 'user_role'
    ];

    protected $hidden = ['created_at','updated_at'];
    
    //includes admin level tabs
    public function tabs()
    {
        return $this->hasMany(\App\Models\Tabs::class, "app_id", "id")
        ->orderBy('sort_order');
    }

    // for app users that have either instructor or admin
    public function instructorroletabs()
    {
        return $this->hasMany(\App\Models\Tabs::class, "app_id", "id")
        ->where(function($query) 
        {
            $query->where("team_role","=", config('constants.INSTRUCTOR'))
            ->orWhere(function($query) 
            {
                $query->where("team_role","=", null)
                ->where('restrict_role',"=",false);
            });
        })
        ->union($this->nullroletabs())
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
        $blankapp = new Apps();
        return $blankapp;
    }


    public static function processUpdates( $appModel)
    {
        $updatedSitePage = null;
        if (isset($appModel['id']) && $appModel['id'] != null) {
            $updateModel = Apps::find($appModel['id']);
            info('updating existing app '. $appModel['id']);
            $updatedSitePage = $updateModel->update( 
            ['team_id' => $appModel['team_id'], 
            'site_id' => $appModel['site_id'],
            'appicon' => $appModel['appicon'], 
            'app_name' => $appModel['app_name'], 
            'page_title' => $appModel['page_title'],
            'page_url' => $appModel['page_url'],
            'pwa_app_url' => $appModel['pwa_app_url'] ?? null,
            'pwa_server_url' => $appModel['pwa_server_url'] ?? null,
            'sort_order' => $appModel['sort_order'] ] );
            info('creating new app');}
            else
        {
            $updatedSitePage = Apps::create(['team_id' => $appModel['team_id'], 
            'site_id' => $appModel['site_id'],
            'appicon' => $appModel['appicon'], 
            'app_name' => $appModel['app_name'], 
            'page_title' => $appModel['page_title'],
            'page_url' => $appModel['page_url'],
            'pwa_app_url' => $appModel['pwa_app_url'] ?? null,
            'pwa_server_url' => $appModel['pwa_server_url'] ?? null,
            'sort_order' => $appModel['sort_order'] ] );
        }
        

        $message = $updatedSitePage ? 'Site Page Updated Successfully.' : 'Site Page Created Successfully.';
        return json_encode($message);
    }

}
