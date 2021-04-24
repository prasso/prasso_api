<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use App\Models\UserActiveApp;
use App\Models\UserRole;
use App\Models\Role;

use Illuminate\Support\Facades\Log;

/**
 * Class User.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $firebase_uid
 * @property string $push_token
 * @property string $profile_photo_path
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasTimestamps;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'profile_photo_url', 'firebase_uid', 'push_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function teams()
    {
        return $this->hasMany(Team::class, 'user_id','id')
            ->with('apps');
    }

    public function roles()
    {
        return $this->hasMany(UserRole::class, 'user_id','id');
    }

    public function activeApp()
    {
        return $this->hasOne( UserActiveApp::class, 'user_id', 'id');
    }

    public function getRouteKeyName() {
        return 'firebase_uid';
    }

    public static function getUserByAccessToken($accessToken)
    {
        return User::select('users.*','users.firebase_uid AS uid')
                ->join('personal_access_tokens', 'users.id', '=', 'personal_access_tokens.tokenable_id')
                ->where('personal_access_tokens.token', '=', $accessToken)
                ->first();
    }

    public function hasRole(...$roles)
    {
        foreach ($this->roles as $role) {
            if (in_array($role->name, $roles)) {
                return true;
            }
        }

        return false;
    }


    public function isSuperAdmin()
    {
        if ($this->hasRole(config('constants.SUPER_ADMIN'))) 
        {
            return true;
        }
        return false;
    }
}
