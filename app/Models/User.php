<?php

namespace App\Models;

use App\Mail\coach_message;
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
use App\Mail\contact_form;
use App\Mail\user_needs_coach;
use Laravel\Cashier\Billable;
use Twilio\Rest\Client;

/**
 * Class User.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $firebase_uid
 * @property string $pn_token
 * @property string $profile_photo_path
 * @property bool $enableMealReminders
 * @property string reminderTimesJson
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 */
class User extends Authenticatable {
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasTimestamps;
    use Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'profile_photo_path', 'firebase_uid', 'pn_token', 'enableMealReminders', 'reminderTimesJson', 'timeZone', 'version'
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
        'profile_photo_path', 'enableMealReminders', 'timeZone'
    ];
    
    /**
     * Get the disk that profile photos should be stored on.
     *
     * @return string
     */
    protected function profilePhotoDisk() {
        return 's3';
    }

    public function getVersionIsNotFirebase() {
        if ($this->version == null || $this->version == 'v1' || $this->version == '') {
            return true;
        }
        return false;
    }

    public function getProfilePhoto() {
        if ($this->profile_photo_path == null) {
            return $this->defaultProfilePhotoUrl();
        }
        if (str_starts_with($this->profile_photo_path, 'http')) {
            return  $this->profile_photo_path;
        }
        return  config('app.photo_url') . $this->profile_photo_path;
    }

    public function teams() {
        return $this->hasMany(Team::class, 'user_id', 'id')
            ->with('apps');
    }

    public function team_member() {
        return $this->hasMany(TeamUser::class, 'user_id', 'id')
            ->with('team');
    }

    public function invitations() {
        return $this->hasMany('App\Models\Invitation');
    }

    public function roles() {
        return $this->hasMany(UserRole::class, 'user_id', 'id');
    }

    public function activeApp() {
        return $this->hasOne(UserActiveApp::class, 'user_id', 'id');
    }

    public function personalAccessToken() {
        return $this->hasOne(PersonalAccessToken::class, 'tokenable_id', 'id');
    }

    public function yourHealthToken() {
        return $this->hasOne(YourHealthToken::class, 'user_id', 'id');
    }

    public function getRouteKeyName() {
        return 'firebase_uid';
    }


    public function getCoachUid() {
        $coachrecord = Team::find($this->current_team_id)->first();
        $coachId = $coachrecord->user_id;
        $coachUid = User::find($coachId)->firebase_uid;
        info('for user ' . $this->id . ' the coachUid is: ' . $coachUid);
        return $coachUid;
    }

    public static function getUserByAccessToken($accessToken) {
        $user = User::select('users.*', 'users.firebase_uid AS uid')
            ->join('personal_access_tokens', 'users.id', '=', 'personal_access_tokens.tokenable_id')
            ->where('personal_access_tokens.token', '=', $accessToken)
            ->first();
        //Log::info('User::getUserByAccessToken: '.json_encode($user));
        return $user;
    }

    public function hasRole(...$role_looking_for) {
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

    public function getUserAppInfo()
    {
        $user_app_info=[];
        $activeApp = $this->activeApp();
  
        $user_app_info['team'] = $this->teams[0];
      
        $user_app_info['teams'] = $this->teams->toArray();
  
        $user_app_info['teamapps'] = $user_app_info['team']->apps;
        
        $activeAppId = '0';
        if (isset($activeApp->app_id))
        {
            $activeAppId = $activeApp->app_id;
        }
        else
        {
            if (count($user_app_info['team']->apps) > 0)
            {
                $activeAppId = $user_app_info['team']->apps[0]->id;
                UserActiveApp::processUpdates($this->id, $activeAppId);
            }
        }
        $user_app_info['activeAppId']=$activeAppId;
  
        return $user_app_info;
    }

    public function fillFromAppjson($user_from_app) {
        if (isset($user_from_app['displayName'])) {
            $this->name = $user_from_app['displayName'];
        } else {
            if (isset($user_from_app['name'])) {
                $this->name = $user_from_app['name'];
            }
        }
        $this->email = $user_from_app['email'];
        $this->profile_photo_path = $user_from_app['photoURL'];
        $this->enableMealReminders = $user_from_app['enableMealReminders'] ? str_replace('\\', '', $user_from_app['enableMealReminders']) : '0';

        if ($this->enableMealReminders = '1') {
            $this->reminderTimesJson = $user_from_app['reminderTimesFromJson'] ?? config('constants.REMINDER_TIMES');
        }


        if (isset($user_from_app['appName']))
        {
            //does this user have a matching app? if not, set it up for them
            //apps are accessed through teams
            $user_app_info = $this->getUserAppInfo();
            //find the app if it exists if it doesn't add it.
            if ($user_app_info['activeAppId'] == '0' || count($user_app_info['teamapps']) == 0)
            {
                $teamapp = Apps::getBlankApp($this);
                $teamapp->app_name = $user_from_app['appName'];
                $teamapp->save;
                UserActiveApp::processUpdates($this->id, $teamapp->id);
            }
        }
    }

    public function sendWelcomeEmail() {
        $emailbcc = 'info@optamize.app'; //because .env setting is not being read on prod server!
        Mail::to($this)->send(new welcome_user($this));

        try {
            Mail::to($emailbcc, 'Optamize Sign Up')->send(new user_needs_coach($this));
        } catch (\Throwable $err) {
            Log::info('error sending coach email: ' . $err);
        }
    }

    public function sendContactFormEmail($subject, $body) {

        Mail::to($this)->send(new contact_form($this, $subject, $body));
    }

    // the user here is the receiver of the email. the email from came from the logged in user
    public function sendCoachEmail($subject, $body, $fromemail, $fromname) {

        Mail::to($this)->send(new coach_message($this, $subject, $body, $fromemail, $fromname));
    }

    // the user here is the receiver of the email. the email from came from the logged in user
    public function sendCoachSms( $body, $fromphone, $phone) 
    {
        $this->sendMessage($body,$fromphone, $phone);
    }
    /**
     * Sends sms to user using Twilio's programmable sms client
     * @param String $message Body of sms
     * @param Number $recipients string or array of phone number of recepient
     */
    private function sendMessage($message,$fromphone, $tophone)
    {
        $account_sid = getenv("TWILIO_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");
        $client = new Client($account_sid, $auth_token);

        info('sendMessage: '.$tophone);
        $client->messages->create('+'.$tophone, 
                ['from' => $fromphone, 'body' => $message] );
    }
}
