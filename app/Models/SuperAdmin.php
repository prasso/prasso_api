<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

class SuperAdmin extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $guard = 'superadmin';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function fetchUserByCredentials($email)
    {

        return self::select('users.*', 'users.firebase_uid AS uid')
            ->join('user_role', 'users.id', '=', 'user_role.user_id')
            ->where('users.email', $email)
            ->where('user_role.role_id', config('constants.SUPER_ADMIN'))
            ->first();
    }

    public static function getUserByAccessToken($accessToken)
    {
        return self::select('users.*', 'users.firebase_uid AS uid')
            ->join('user_role', 'users.id', '=', 'user_role.user_id')
            ->where('personal_access_tokens.token', $accessToken)
            ->where('user_role.role_id', config('constants.SUPER_ADMIN'))
            ->first();
    }
}
