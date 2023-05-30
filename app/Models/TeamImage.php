<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamImage extends Model
{
    use HasFactory;
    protected $table = 'team_images';

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
