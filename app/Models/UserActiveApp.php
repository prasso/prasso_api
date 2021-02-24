<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Apps;

class UserActiveApp extends Model
{
    use HasFactory;

    protected $table = 'user_active_app';

    protected $fillable = [
        'user_id', 'app_id'
    ];

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function app()
    {
        return $this->belongsTo(Apps::class);
    }

    public static function processUpdates($userid, $appid)
    {

        $updatedata = UserActiveApp::firstOrNew(['user_id'=>$userid]);
        $updatedata->app_id = $appid;
        $updatedata->save();
        return $updatedata;
    }
}
