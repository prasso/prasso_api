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
use App\Models\PersonalAccessToken;
use App\Models\UserRole;
use App\Models\TeamUser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\welcome_user;
use App\Mail\user_needs_coach;
//use Laravel\Cashier\Billable;

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
    //use Billable;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'profile_photo_path', 'firebase_uid', 'push_token','timeZone'
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
        'reminderTimesJson'
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

    protected $appends = [
        'profile_photo_path', 'timeZone'
    ];
    
    /**
     * Get the disk that profile photos should be stored on.
     *
     * @return string
     */
    protected function profilePhotoDisk()
    {
        return 's3';
    }

    public function getProfilePhoto()
    {
        if ($this->profile_photo_path == null)
        {
            return $this->defaultProfilePhotoUrl();
        }
        if (str_starts_with($this->profile_photo_path,'http'))
        {
            return  $this->profile_photo_path;
        }
        return  config('app.photo_url').$this->profile_photo_path;
    }

    public function teams()
    {
        return $this->hasMany(Team::class, 'user_id','id')
            ->with('apps');
    }

    public function team_member()
    {
        return $this->hasMany(TeamUser::class, 'user_id','id')
            ->with('team');
    }

    public function invitations() {
        return $this->hasMany('App\Models\Invitation');
    }

    public function roles()
    {
        return $this->hasMany(UserRole::class, 'user_id','id');
    }

    public function activeApp() 
    {
        return $this->hasOne(UserActiveApp::class, 'user_id', 'id');
    }

    public function personalAccessToken() 
    {
        return $this->hasOne(PersonalAccessToken::class, 'tokenable_id', 'id');
    }

    public function getRouteKeyName() {
        return 'firebase_uid';
    }

    public static function getUserByAccessToken($accessToken)
    {
        $user = User::select('users.*','users.firebase_uid AS uid')
                ->join('personal_access_tokens', 'users.id', '=', 'personal_access_tokens.tokenable_id')
                ->where('personal_access_tokens.token', '=', $accessToken)
                ->first();
        //Log::info('User::getUserByAccessToken: '.json_encode($user));
        return $user;
    }

    public function hasRole(...$role_looking_for)
    {
       //Log::info('received roles: ' . json_encode($role_looking_for));
        
        foreach ($this->roles as $role) {

           //Log::info('role: ' . json_encode($role));
            
            if (in_array($role->role_id, $role_looking_for)) {
                return true;
            }
        }

        return false;
    }

    public function isSuperAdmin() {
        if ($this->hasRole(config('constants.SUPER_ADMIN'))) {
            return true;
        }
        return false;
    }
    public function isInstructor() {
       // Log::info('checking for isInstructor: ' );
        
        if ($this->hasRole(config('constants.INSTRUCTOR'))) {
            return true;
        }
        if ($this->hasRole(config('constants.SUPER_ADMIN'))) {
            return true;
        }
        return false;
    }

    public function fillFromAppjson($user_from_app)
    {
        if (isset($user_from_app['displayName']))
        { 
            $this->name = $user_from_app['displayName'];
        }
        else
        {
            if (isset($user_from_app['name']))
            { 
                $this->name = $user_from_app['name'];
            }
        }
        $this->email = $user_from_app['email'];
        $this->profile_photo_path= $user_from_app['photoURL'];
       
   
    }

    public function sendWelcomeEmail()
    {
        $emailbcc = 'info@prasso.io'; //because .env setting is not being read on prod server!
        Mail::to($this)->send(new welcome_user($this));

        try{
        Mail::to($emailbcc,'Prasso Sign Up')->send(new user_needs_coach($this));
        }
        catch(\Throwable $err)
        {
            Log::info('error sending coach email: '.$err);
        }
        
    }
    
}
