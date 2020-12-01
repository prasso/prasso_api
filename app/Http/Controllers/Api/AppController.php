<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Log;

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
}
