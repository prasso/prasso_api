<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as BaseController;
use App\Services\AppsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;


class AppController extends BaseController
{
    protected $appsService;
    protected $site;

    public function __construct(Request $request, AppsService $appSPe)
    {
        parent::__construct( $request);
        $this->appsService =  $appSPe;

    }

    public function getAppSettings($apptoken)
    {
        try {
          $user = \Auth::user();
          $app_data = $this->appsService->getAppSettingsBySite($this->site, $user,$user->personalAccessToken->token);
            Log::info('AppController::getAppSettings: '.$app_data);
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
