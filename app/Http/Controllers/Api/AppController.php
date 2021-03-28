<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Services\AppsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;

class AppController extends Controller
{
    protected $appsService;

    public function __construct(AppsService $appSP)
    {
        $appsService =  $appSP;
    }

    public function getAppSettings($apptoken)
    {
        try {
            $app_data = $this->appsService->getAppSettings($apptoken);
            return $app_data;
        } catch (\Throwable $e) {
            Log::info($e);
        }
    }

    public function saveApp(Request $request)
    {
        try {

            $success = $this->appsService->saveApp($request);
            
            return $this->sendResponse($success, 'App saved.');
        } catch (\Throwable $e) {
            Log::info($e);
        }
    }
}
