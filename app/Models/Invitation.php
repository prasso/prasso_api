<?php

namespace App\Models;

use App\Traits\InvitesTeamMembers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Str;

class Invitation extends Model {
    use HasFactory;
    use Notifiable;
    use InvitesTeamMembers;
    protected $fillable = [
        'user_id',
        'team_id',
        'role',
        'email',
        'code',
    ];
    protected static function booted() {
        //Log::info('invitation booted');
        static::creating(function ($invitation) {
            $invitation->code = $invitation->code ?: Str::random(40);
            return $invitation;
        });
    }
    public function user() {
        return $this->belongsTo('App\Models\User');
    }
    public function team() {
        return $this->belongsTo('App\Models\Team');
    }
}
