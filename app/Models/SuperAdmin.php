<?php

namespace App\Models;
use Illuminate\Support\Facades\Log;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuperAdmin extends User
{
    
    protected $table = 'users';

    protected $guard = 'super_admin';

    public function __construct(array $attributes = array()){
        parent::__construct($attributes);
     }
 
     public static function fetchUserByCredentials($username)
     {
         return User::select('users.*','users.firebase_uid AS uid')
                 ->join('user_role', 'users.id','=','user_role.user_id')
                 ->where('users.email', '=', $username)
                 ->where('user_role.role_id','=',config('constants.SUPER_ADMIN_ROLE'))
                 ->first();
     }

    public static function getUserByAccessToken($accessToken)
    {
        return User::select('users.*','users.firebase_uid AS uid')
                ->join('user_role', 'users.id','=','user_role.user_id')
                ->where('personal_access_tokens.token', '=', $accessToken)
                ->where('user_role.role_id','=',config('constants.SUPER_ADMIN_ROLE'))
                ->first();
    }
}
