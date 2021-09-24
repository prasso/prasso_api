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
        return User::select('users.firebase_uid as uid', 'users.id')
                ->join('team_user', 'users.id','team_user.user_id')
                ->where('team_user.team_id','=',$team_id)
                ->get()->toArray();

    }
}