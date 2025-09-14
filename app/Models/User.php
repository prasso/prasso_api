<?php

namespace App\Models;

use App\Mail\coach_message;
use Illuminate\Support\Facades\Log;
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
use Illuminate\Support\Facades\Mail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use App\Mail\welcome_user;
use App\Mail\contact_form;
use App\Mail\prasso_user_welcome;
use App\Mail\livestream_notification;
use App\Mail\site_data_updated_email;
use Laravel\Cashier\Billable;
use Twilio\Rest\Client;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;


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
class User extends Authenticatable implements FilamentUser, HasAvatar
{
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
        'name',
        'email',
        'password',
        'profile_photo_path',
        'firebase_uid',
        'pn_token',
        'version',
        'phone'

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
        'password' => 'hashed',
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
    protected function profilePhotoDisk()
    {
        return 's3';
    }

    public function getVersionIsNotFirebase()
    {
        if ($this->version == null || $this->version == 'v1' || $this->version == '') {
            return true;
        }
        return false;
    }

    public function getProfilePhotoUrlAttribute()
    {
        if (!$this->profile_photo_path) {
            return $this->defaultProfilePhotoUrl();
        }
        if (stripos($this->profile_photo_path, 'http') === 0) {
            return rtrim($this->profile_photo_path, '/');
        }

        return rtrim(config('app.photo_url'), '/') . '/' . ltrim($this->profile_photo_path, '/');
    }
    public function getProfilePhoto()
    {
        return $this->getProfilePhotoUrlAttribute();
    }
    public function getFilamentAvatarUrl(): ?string
    {
        return  $this->getProfilePhoto();
    }


    public function team_owner()
    {
        return $this->hasMany(Team::class, 'user_id', 'id')
            ->whereHas('team_members', function ($query) {
                $query->where('role', 'instructor');
            })
            ->with(['apps', 'site' => function ($query) {
                $query->when(auth()->user()->role != 'super_admin', function ($query) {
                    $query->where('site_id', '!=', 1);
                });
            }]);
    }

    public function team_member()
    {
        return $this->hasMany(TeamUser::class, 'user_id', 'id')
            ->with('team');
    }

    public function invitations()
    {
        return $this->hasMany('App\Models\Invitation');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role', 'user_id', 'role_id');
    }

    public function activeApp()
    {
        return $this->hasOne(UserActiveApp::class, 'user_id', 'id');
    }

    public function getPersonalAccessTokenAttribute()
    {
        $token = $this->personalAccessToken()->first();
        if (!$token) {
            $token = $this->createPersonalAccessToken();
        }
        return $token->token;
    }

    protected function createPersonalAccessToken()
    {
        $token = $this->createToken('default');
        $personalAccessToken = new SanctumPersonalAccessToken();
        $personalAccessToken->tokenable_id = $this->id;
        $personalAccessToken->tokenable_type = get_class($this);
        $personalAccessToken->name = 'default';
        $personalAccessToken->token = $token->plainTextToken;
        $personalAccessToken->abilities = ['*'];
        $personalAccessToken->save();

        return $personalAccessToken;
    }

    public function personalAccessToken()
    {
        return $this->hasOne(PersonalAccessToken::class, 'tokenable_id', 'id');
    }

    public function getRouteKeyName()
    {
        return 'id';
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

    public function getCoachUid()
    {
        $coachrecord = Team::find($this->current_team_id)->first();
        $coachId = $coachrecord->user_id;
        $coachUid = User::find($coachId)->firebase_uid;
        return $coachUid;
    }

    public static function getUserByAccessToken($accessToken)
    {
        $user = User::select('users.*', 'users.firebase_uid AS uid')
            ->join('personal_access_tokens', 'users.id', '=', 'personal_access_tokens.tokenable_id')
            ->where('personal_access_tokens.token', '=', $accessToken)
            ->first();
        //Log::info('User::getUserByAccessToken: '.json_encode($user));
        return $user;
    }

    public function hasRole(...$role_looking_for)
    {
        foreach ($this->roles as $role) {
            $roleId = (int) $role->id; // Cast role_id to integer
            if (in_array($roleId, $role_looking_for)) {
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
   

    public function isTeamOwner($team){
        return $this->id == $team->user_id;
    }
    public function isTeamOwnerForSite($site){
        $firstTeam = $site->teams()->first();
        if ($firstTeam && $this->team_owner->pluck('id')->contains($firstTeam->id)) {
            return true;
        } 
        return false;
    }

    // User.php
    public function isTeamMember($teamId) {
        return $this->team_member()->where('team_id', $teamId)->exists();
    }

    public function isTeamMemberOrOwner($teamId) {
        if ($this->isSuperAdmin()) {
            return true;
        }
        $isMember = $this->team_member()->where('team_id', $teamId)->exists();
        $isOwner = $this->team_owner()->where('id', $teamId)->exists();
        return $isMember || $isOwner;
    }
/**
 * Check if user has instructor-level access
 * 
 * Super Admins always have instructor access. For regular instructors,
 * if a site is provided, checks if they are a member of any of the site's teams.
 * If no site is provided, just checks if they have the instructor role.
 *
 * @param  \App\Models\Site|null  $site  Optional site to check team membership
 * @return bool
 */
public function isInstructor(?\App\Models\Site $site = null)
{
    $this->load('roles');

    // Super admins always have instructor access
    if ($this->hasRole(config('constants.SUPER_ADMIN'))) {
        return true;
    }

    // If user doesn't have instructor role, they're not an instructor
    if (!$this->hasRole(config('constants.INSTRUCTOR'))) {
        return false;
    }

    // If no site is provided, just check the instructor role
    if (!$site) {
        \Log::info("No site provided, user {$this->id} has INSTRUCTOR role, granting access");
        return true;
    }

    // Check team membership for the specific site
    $siteTeams = $site->teams()->pluck('teams.id');

    foreach ($siteTeams as $teamId) {
        if ($this->isTeamMember($teamId)) {
            return true;
        }
    }

    return false;
}


    /**
     * Determine if the user belongs to the given team.
     *
     * @param  mixed  $team
     * @return bool
     */
    public function belongsToTeam($team)
    {
        info('local belongs to team');
        if (is_null($team)) {
            return false;
        }

        // Super admins can access any team
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Check if the user owns or is a member of this specific team
        $isTeamMember = $this->ownsTeam($team) || $this->teams->contains(function ($t) use ($team) {
            return $t->id === $team->id;
        });

        // If not a direct team member, check if they're trying to access Prasso team data
        if (!$isTeamMember && $team->id == 1) {
            // Prevent site admins from accessing Prasso team data unless they're specifically
            // members of the Prasso team
            return false;
        }

        return $isTeamMember;
    }

    /**filament interface, can the user access filament admin */
    public function canAccessPanel(Panel $panel): bool
    {
        $panelId = method_exists($panel, 'getId') ? $panel->getId() : null;

        // Super Admin panel (global controls)
        if ($panelId === 'admin') {
            return $this->isSuperAdmin();
        }

        // Site Admin panel (site owners / instructors scoped to own site)
        if ($panelId === 'site-admin') {
            // Super admins can access any site admin panel
            if ($this->isSuperAdmin()) {
                return true;
            }

            // Get the current site based on the host
            $host = request()->getHttpHost();
            $currentSite = \App\Models\Site::getClient($host);

            if (!$currentSite) {
                return false;
            }

            // If this is the Prasso site (ID 1), only super admins should access it
            // (super admins are already handled above)
            if ($currentSite->id == 1) {
                return false;
            }

            // For other sites, check if the user is a team owner for this specific site
            return $this->isInstructor($currentSite);
        }

        // Default deny for unknown panels
        return false;
    }

    public function getUserAppInfo()
    {
        $user_app_info = [];
        $activeApp = $this->activeApp();

        $user_app_info['team'] = $this->team_owner[0];

        $user_app_info['teams'] = $this->team_owner->toArray();

        $user_app_info['teamapps'] = $user_app_info['team']->apps;

        $activeAppId = '0';
        if (isset($activeApp)) {
            $activeAppId = $activeApp->app_id;
        } else {
            if (count($user_app_info['team']->apps) > 0) {
                $activeAppId = $user_app_info['team']->apps[0]->id;
                UserActiveApp::processUpdates($this->id, $activeAppId);
            }
        }
        $user_app_info['activeAppId'] = $activeAppId;

        return $user_app_info;
    }

    public function fillFromAppjson($user_from_app)
    {
        if (isset($user_from_app['displayName'])) {
            $this->name = $user_from_app['displayName'];
        } else {
            if (isset($user_from_app['name'])) {
                $this->name = $user_from_app['name'];
            }
        }
        $this->email = $user_from_app['email'];
        $this->profile_photo_path = $user_from_app['photoURL'];

        if (isset($user_from_app['appName'])) {
            //does this user have a matching app? if not, set it up for them
            //apps are accessed through teams
            $user_app_info = $this->getUserAppInfo();
            //find the app if it exists if it doesn't add it.
            if ($user_app_info['activeAppId'] == '0' || count($user_app_info['teamapps']) == 0) {
                $teamapp = Apps::getBlankApp($this);
                $teamapp->app_name = $user_from_app['appName'];
                $teamapp->save;
                UserActiveApp::processUpdates($this->id, $teamapp->id);
            }
        }
    }

    public function app()
    {
        return $this->hasOne(Apps::class);
    }

    public function getSiteCount()
    {
        if ($this->isSuperAdmin()) {
            return 1;
        }
        if ($this->team_owner == null) {
            return 0;
        }
        $teams = $this->team_owner->toArray();
        $site_count = 0;
        foreach ($teams as $team) {
            $site_count += count($team['site']);
        }
        return $site_count;
    }
    public function canManageTeamForSite($team_id)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        if ($this->team_owner == null) {
            info('team_owner is null');
            return false;
        }
        $teams = $this->team_owner->toArray();
        foreach ($teams as $team) {
            if ($team_id == $team['id'] && $team['user_id'] == $this->id) {
                return true;
            }
        }

        return false;
    }
    /**
     * Get/Set the user's current team. This is the first team that is owned by the user, if it exists
     * at the time of this writing, only one team per user that is not a super admin is allowed
     */
    public function setCurrentToOwnedTeam()
    {
        if ($this->id == 1) {
            //super admin, ok
            return;
        }
        $this->load('team_owner');
        if (isset($this->current_team_id) && $this->current_team_id > 1) {
            return;
        }
        $teams = [];
        if ($this->team_owner != null && count($this->team_owner) > 0) {
            info('user: ' . $this->id . ' has owned teams.');
            $teams = $this->team_owner->toArray();
        }
        if ($this->current_team_id == null) {
            $this->current_team_id = 1;
        }

        if (count($teams) == 0) {

            info('user: ' . $this->id . ' has 0 owned teams.');
            $teamids = $this->team_member->toArray();
            info('teamids: ' . json_encode($teamids));
            if (count($teamids) > 0) {
                $this->current_team_id = $teamids[0]['team_id'];
            }
        } else {
            if ($this->current_team_id == 1) {

                foreach ($teams as $team) {
                    if ($team['user_id'] == $this->id) {
                        $this->current_team_id = $team['id'];
                        info('team owner of: ' . $team['id']);
                        break;
                    }
                }
            }
        }
        $this->save();
    }

    /**
     * Get the user's site url. 
     * determined by two things. 
     *  1. if this is prasso then the site url is the first team that is owned by the user
     *  2. if this is not prasso then if the user owns the current site's team then the same site as is showing
     * at the time of this writing, only one team per user that is not a super admin is allowed
     */
    public function getUserOwnerSiteId()
    {
        $this->setCurrentToOwnedTeam();
        $teamsite = TeamSite::where('team_id', $this->current_team_id)->first();
        return $teamsite->site_id;
    }

    public function isThisSiteTeamOwner($site_id)
    {
        if ($site_id == 1) {
            return false;
        } // only super-admins own prasso and that is checked first
        /*
   
   if the site has subteams rules are the same as when the site does not
    the site owner user_id is stored with the team record

    is the problem knowing which team owns. then look at parent record of the team
    */
        $this->load('team_owner');
        $teams = $this->team_owner->toArray();

        foreach ($teams as $team) {
            if ($team['user_id'] == $this->id) {
                $teamsite = TeamSite::where('team_id', $team['id'])->where('site_id', $site_id)->first();
                if ($teamsite != null) {
                    return true;
                }
            }
        }
        return false;
    }

    public function sendWelcomeEmail($site_team_id)
    {
        $emailbcc = 'info@faxt.com'; //because .env setting is not being read on prod server!
        Mail::to($this)->send(new welcome_user($this));

        try {
            Mail::to($emailbcc, 'Prasso Sign Up')->send(new prasso_user_welcome($this, $site_team_id));
        } catch (\Throwable $err) {
            Log::info('error sending user welcome sent email: ' . $err);
        }
    }

    public function sendContactFormEmail($subject, $body)
    {

        Mail::to($this)->send(new contact_form($this, $subject, $body));
    }

    // the user here is the receiver of the email. the email from came from the logged in user
    public function sendCoachEmail($subject, $body, $fromemail, $fromname)
    {

        Mail::to($this)->send(new coach_message($this, $subject, $body, $fromemail, $fromname));
    }

    public function sendDataUpdated($message, $site)
    {
        //email from current user and to team admin from updates to site page data
        try {
            $team_admin =  $site->getTeamOwner();
            if ($team_admin == null) {
                info('team admin not found: ' . $site->site_name);
                return;
            }
            //site team-0 owner
            Mail::to($team_admin)->send(new site_data_updated_email(
                $this,
                $site->site_name . config('constants.DATA_UPDATED_SUBJECT'),
                $message,
                $this->email,
                $this->name
            ));
        } catch (\Throwable $err) {
            Log::info('error sending user welcome sent email: ' . $err);
        }
    }

    public function sendLivestreamNotification($subject, $body, $fromemail, $fromname)
    {

        Mail::to($this)->send(new livestream_notification($this, $subject, $body, $fromemail, $fromname));
    }

    // the user here is the receiver of the email. the email from came from the logged in user
    public function sendCoachSms($body, $fromphone, $phone)
    {
        $this->sendMessage($body, $fromphone, $phone);
    }
    /**
     * Sends sms to user using Twilio's programmable sms client
     * @param String $message Body of sms
     * @param Number $recipients string or array of phone number of recepient
     */
    private function sendMessage($message, $fromphone, $tophone)
    {
        $account_sid = getenv("TWILIO_ACCOUNT_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");
        $client = new Client($account_sid, $auth_token);

        info('sendMessage: ' . $tophone);
        $client->messages->create(
            '+' . $tophone,
            ['from' => $fromphone, 'body' => $message]
        );
    }
}
