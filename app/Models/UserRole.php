<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserRole extends Model
{
    use HasFactory;
    use HasTimestamps;

    protected $table = "user_role";

       /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'role_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function team_role() {
        return $this->belongsTo(Role::class, 'role_id');
    }
    /*
    commenting out as I believe this was a mistake, but maybe is 
    a necessary relation for the Tabs class which is the table
    that feeds the apps and if the team_role field is filled in there then
    the app will limit who can view it
    public function team_role() {
        return $this->belongsTo(Tab::class, 'team_role');
    }*/

}
