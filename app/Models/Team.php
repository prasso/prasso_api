<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;

class Team extends JetstreamTeam
{
    use HasTimestamps;
    use HasFactory;
    
    /**
     * 
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'personal_team' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'personal_team',
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['timestamp'];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    public function apps()
    {
        return $this->hasMany(\App\Models\Apps::class, "team_id", "id")->with('tabs');
    }

    public function site()
    {
        return $this->hasMany(\App\Models\TeamSite::class, "team_id", "id")->with('site');
    }

    public function team_members()
    {
        return $this->hasMany(\App\Models\TeamUser::class, 'team_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function invitations() {
        return $this->hasMany('App\Models\Invitation');
    }

    public function images()
    {
        return $this->hasMany(TeamImage::class);
    }
}
