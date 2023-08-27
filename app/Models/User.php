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
use App\Mail\prasso_user_welcome;
use App\Mail\livestream_notification;
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
    protected $fillable = [    'name', 'email', 'password', 'profile_photo_path', 'firebase_uid', 'pn_token', 'version','phone'
    
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
        'profile_photo_path'
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

    public function team_owner() {
        return $this->hasMany(Team::class, 'user_id', 'id')
            ->with('apps')->with('site');
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

    public function assignRole($roleName)
    {
        // Find the role with the specified name.
        $role = Role::where('role_name', $roleName)->first();

        // If the role doesn't exist, throw an exception.
        if (!$role) {
            throw new \InvalidArgumentException("Role not found: $roleName");
        }

        // Check if the user already has the role.
        if ($this->roles->contains($role)) {
            return;
        }

        // Assign the role to the user.
        $new_role = UserRole::create(['user_id' => $this->id, 'role_id' => $role->id]);
        $new_role->save($role->toArray());
    }

    public function getCoachUid() {
        $coachrecord = Team::find($this->current_team_id)->first();
        $coachId = $coachrecord->user_id;
        $coachUid = User::find($coachId)->firebase_uid;
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
        foreach ($this->roles as $role) {

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

    public function isTeamOwnerForSite($site){
        $firstTeam = $site->teams()->first();
        if ($firstTeam && $this->team_owner->pluck('id')->contains($firstTeam->id)) {
            // The user is the owner of the first team that belongs to the site.
            // the first team is assigned on site creation and is the only team that will edit the site setup
            return true;
        } 
        return false;
    }

    public function getUserAppInfo()
    {
        $user_app_info=[];
        $activeApp = $this->activeApp();
  
        $user_app_info['team'] = $this->team_owner[0];
      
        $user_app_info['teams'] = $this->team_owner->toArray();
  
        $user_app_info['teamapps'] = $user_app_info['team']->apps;
        
        $activeAppId = '0';
        if (isset($activeApp))
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
    public function getSiteCount() {
        if ($this->isSuperAdmin())
        {
            return 1;
        }
        if ($this->team_owner == null)
        {
            return 0;
        }
        $teams = $this->team_owner->toArray();
        $site_count = 0;
        foreach($teams as $team)
        {
            $site_count += count($team['site']);
        }
        return $site_count;
    }
    public function canManageTeamForSite(){
        if ($this->isSuperAdmin())
        {
            return true;
        }
        if ($this->team_owner == null)
        {
            info('team_owner is null');
            return false;
        }
        $teams = $this->team_owner->toArray();
        foreach($teams as $team)
        {
            if ($team['user_id'] == $this->id)
            {
                return true;
            }
        }
        info('canManageTeamForSite returning false');
        return false;
    }
    /**
     * Get/Set the user's current team. This is the first team that is owned by the user, if it exists
     * at the time of this writing, only one team per user that is not a super admin is allowed
     */
    public function setCurrentTeam()
    {
        if ($this->current_team_id != null) {
            return;
        }
        $teams = [];
        if ($this->team_owner != null && count($this->team_owner)>0){
            $teams = $this->team_owner->toArray();
        }
        if ($this->current_team_id == null) {
            $this->current_team_id = 1;
        }
        if (count($teams) == 0) {
            $teamids = $this->team_member->toArray();
info('teamids: ' . json_encode($teamids));
            if (count($teamids) > 0) {
                $this->current_team_id = $teamids[0]['team_id'];
            }
        } else {
            if ($this->current_team_id == 1) {
                foreach ($teams as $team) {
                    if ($team['user_id'] == $this->id) {
                        $this->current_team_id = $team['team_id'];
                    }
                    break;
                }
            }
        }
        $this->save();
    }

    /**
     * Get the user's site url. This is the first team that is owned by the user
     * at the time of this writing, only one team per user that is not a super admin is allowed
     */
    public function getUserSiteUrl(){
        $this->setCurrentTeam();
        
        $teamsite = TeamSite::where('team_id', $this->current_team_id)->first();
        $site = Site::where('id', $teamsite->site_id)->first();
        
        $site_url = $site['host'];
        
        
        return "https://$site_url";
    }

    public function isThisSiteTeamOwner($site_id) {
        $teams = $this->team_owner->toArray();
        foreach($teams as $team)
        {
            if ($team['user_id'] == $this->id)
            {
                $teamsite = TeamSite::where('team_id', $team['id'])->where('site_id', $site_id)->first();
                if ($teamsite != null)
                {
                    return true;
                }
            }
        }
        return false;
    }

    public function sendWelcomeEmail($site_team_id) {
        $emailbcc = 'info@faxt.com'; //because .env setting is not being read on prod server!
        Mail::to($this)->send(new welcome_user($this));

        try {
            Mail::to($emailbcc, 'Prasso Sign Up')->send(new prasso_user_welcome($this, $site_team_id));
        } catch (\Throwable $err) {
            Log::info('error sending user welcome sent email: ' . $err);
        }
    }

    public function sendContactFormEmail($subject, $body) {

        Mail::to($this)->send(new contact_form($this, $subject, $body));
    }

    // the user here is the receiver of the email. the email from came from the logged in user
    public function sendCoachEmail($subject, $body, $fromemail, $fromname) {

        Mail::to($this)->send(new coach_message($this, $subject, $body, $fromemail, $fromname));
    }

    public function sendLivestreamNotification($subject, $body, $fromemail, $fromname) {

        Mail::to($this)->send(new livestream_notification($this, $subject, $body, $fromemail, $fromname));
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
        $account_sid = getenv("TWILIO_ACCOUNT_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");
        $client = new Client($account_sid, $auth_token);

        info('sendMessage: '.$tophone);
        $client->messages->create('+'.$tophone, 
                ['from' => $fromphone, 'body' => $message] );
    }
}
