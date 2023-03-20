<?php

namespace App\Models;
use Illuminate\Support\Facades\Log;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instructor extends User
{
    
    protected $table = 'users';

    protected $guard = 'instructor';

    public function __construct(array $attributes = array()){
        parent::__construct($attributes);
     }
 
     public static function canAccessSite($instructor, $site){

        if ($site == null){
            return false;
        }
        if ($instructor->isSuperAdmin()){

            return true;
        }

        $team = $site->team;
        if ($team == null){
            return false;
        }
        $teamUser = TeamUser::where('team_id', $team->id)->where('user_id', $instructor->id)->first();
        if ($teamUser == null){
            return false;
        }
        return true;
     }

     public function setupAsInstructor($user)
     {
         $userExistingInstructorRole = $user->roles()->where('name', 'instructor')->first();
        if (!isset($userExistingInstructorRole))
        {
            UserRole::forceCreate([
            'user_id' => $user->id,
            'role_id' => config('constants.INSTRUCTOR')
          ]);
        }
        TeamUser::removeTeamMembership($user, config('constants.DEFAULT_COACH_TEAM_ID'));
        $personalteam = TeamUser::where('user_id','=', $user->id)->first();
        $user->current_team_id = $personalteam->team_id;
        $user->save();
        //this user's personal team is now this user's team
        
     }
     
     public function fetchUserByCredentials($email) {
        $instruc = User::select('users.*','users.firebase_uid AS uid')
                 ->join('user_role', 'users.id','=','user_role.user_id')
                 ->where(function($query) use ($email)
                 {
                     $query->where('users.email', '=', $email)
                            ->where('user_role.role_id','=',config('constants.SUPER_ADMIN'));
                 })
                 ->orWhere(function($query) use ($email)
                 {
                    $query->where('users.email', '=', $email)
                    ->where('user_role.role_id','=',config('constants.INSTRUCTOR'));
                 })
                 ->first();
        return $instruc;
     }

    public static function getUserByAccessToken($accessToken)
    {
        return User::select('users.*','users.firebase_uid AS uid')
                ->join('user_role', 'users.id','=','user_role.user_id')
                ->where(function($query) use ($accessToken)
                {
                    $query->where('personal_access_tokens.token', '=', $accessToken)
                          ->where('user_role.role_id','=',config('constants.SUPER_ADMIN'));
                })
                ->orWhere(function($query) use ($accessToken)
                {
                    $query->where('personal_access_tokens.token', '=', $accessToken)
                          ->where('user_role.role_id','=',config('constants.INSTRUCTOR'));
                })
                ->first();
    }

    public static function getTeamMembersFor($team_id)
    {
        info('returning members for team_id: '.$team_id);
        return User::select('users.firebase_uid as uid', 'users.id', 'users.name', 'users.email')
                ->join('team_user', 'users.id','team_user.user_id')
                ->where('team_user.team_id','=',$team_id)
                ->get()->toArray();

    }
}