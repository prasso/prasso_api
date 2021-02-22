<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserActiveApp extends Model
{
    use HasFactory;

    protected $table = 'user_active_app';
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
