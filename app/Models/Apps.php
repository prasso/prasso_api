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
        'team_id', 'appicon', 'app_name', 'page_title', 'page_url', 'sort_order'
    ];

    public function tabs()
    {
        return $this->hasMany(\App\Models\Tabs::class, "app_id", "id")->orderBy('sort_order');
    }
    
    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }

    public function activeApp()
    {
        return $this->hasOne( UserActiveApp::class, 'app_id', 'id');
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
