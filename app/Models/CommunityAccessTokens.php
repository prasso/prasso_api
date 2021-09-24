<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityAccessTokens extends Model
{
    use HasFactory;

    protected $table = "communityaccess_tokens";
    public $timestamps = false;
}
