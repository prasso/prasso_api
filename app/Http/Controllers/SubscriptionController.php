<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Auth;
use App\Services\UserService;
use App\Services\AppsService;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends BaseController
{
    protected $userService;
    protected $appsService;
 
    public function __construct(Request $request, UserService $userServ, AppsService $appsServ)
    {
        parent::__construct( $request);

        $this->middleware('instructorusergroup');
        $this->userService = $userServ;
        $this->appsService = $appsServ;
    }

  
    public function save_subscription(Request $request)
    {        
        $data = $request->all();
        
        info('save_subscription: '.json_encode($data));
        $user = Auth::user();

        $subject = 'Subscription Posted';
        $body = json_encode($data);
info('save_subscription: '.$body);

        $admin_user = \App\Models\User::where('email','bcp@faxt.com')->first();
        $admin_user -> sendContactFormEmail($subject, $body);

        $userresponse = $this->userService->addOrUpdateSubscription($request, $user, $this->appsService, $this->site);
        $body = 'returning this from save_subscription: '.json_encode($userresponse);

        $admin_user -> sendContactFormEmail($subject, $body);

        return $this->sendResponse($userresponse, 'ok');
    }

}
