<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Handler;
use App\Http\Controllers\Controller;
use App\Models\Apps;
use DB;
use Illuminate\Http\Request;

class AppController extends Controller
{
    public function getAppSettings($apptoken)
    {
        try {
            $app = DB::table('personal_access_tokens')->where('token', '=', $apptoken)->first();
            if ($app == null)
            {
                //bad access token, return 404
                abort(404);
            }

            $app_data = Apps::with('tabs')
                ->where('token_id',$app->id)
                ->get();
           
            return \Illuminate\Support\Facades\Response::json($app_data);
        } catch (\Throwable $e) {
            Handler.report($e);
        }
    }
}
