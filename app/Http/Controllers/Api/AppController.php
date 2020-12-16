<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class AppController extends Controller
{
    public function getAppSettings($apptoken)
    {
        try {
            $app_data = AppServiceProvider::getAppSettings($apptoken);
            return $app_data;
        } catch (\Throwable $e) {
            Log::info($e);
        }
    }

    public function saveApp(Request $request)
    {
        try {

            $success = AppServiceProvider::saveApp($request);
            
            return $this->sendResponse($success, 'App saved.');
        } catch (\Throwable $e) {
            Log::info($e);
        }
    }
}
