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
        return $this->hasMany(\App\Models\Tabs::class, "app_id", "id");
    }
    
    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }

    public static function processUpdates( $teamModel)
    {
        Apps::updateOrCreate(['id' => $teamModel['id']] , 
        ['team_id' => $teamModel['team_id'], 
        'appicon' => $teamModel['appicon'], 
        'app_name' => $teamModel['app_name'], 
        'page_title' => $teamModel['page_title'],
        'page_url' => $teamModel['page_url'],
        'sort_order' => $teamModel['sort_order'] ] );
    }
}
