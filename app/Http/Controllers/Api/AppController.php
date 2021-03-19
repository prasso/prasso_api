<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class AppController extends Controller
{
    protected $appServiceProvider;

    public function __construct(AppServiceProvider $appSP)
    {
        $appServiceProvider =  $appSP;
    }

    public function getAppSettings($apptoken)
    {
        try {
            $app_data = $this->appServiceProvidergetAppSettings($apptoken);
            return $app_data;
        } catch (\Throwable $e) {
            Log::info($e);
        }
    }

    public function saveApp(Request $request)
    {
        try {

            $success = $this->appServiceProvidersaveApp($request);
            
            return $this->sendResponse($success, 'App saved.');
        } catch (\Throwable $e) {
            Log::info($e);
        }
    }
}
