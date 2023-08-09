<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamSite extends Model
{
    use HasFactory;

    protected $table = 'team_site';
    
    protected $fillable = [
        'team_id', 'site_id'
    ];

    public function team()
    {
        return $this->belongsTo(\App\Models\Team::class, 'team_id', 'id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id', 'id');
    }
}
