<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Handler;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Providers\AppServiceProvider;

class AppController extends Controller
{
    public function getAppSettings($apptoken)
    {
        try {
            $app_data = AppServiceProvider::getAppSettings($apptoken);
            return $app_data;
        } catch (\Throwable $e) {
            Handler.report($e);
        }
    }
}
