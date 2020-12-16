<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/*
    Apps have Tabs
*/
class Apps extends Model
{
    use HasFactory;
    protected $fillable = [
        'team_id', 'appicon', 'app_name', 'page_title', 'page_url', 'sort_order',
    ];

    public function tabs()
    {
        return $this->hasMany(\App\Models\Tabs::class, "app_id", "id");
    }
    
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
