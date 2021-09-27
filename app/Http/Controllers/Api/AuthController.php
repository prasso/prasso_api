<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Validator;
use Illuminate\Support\Facades\Log;
use App\Services\AppsService;
use App\Services\SitePageService;
use App\Services\UserService;
use App\Models\SitePages;
use App\Models\Site;
use Illuminate\Database\Eloquent\JsonEncodingException;
use App\Actions\Fortify\CreateNewUser;

class AuthController extends BaseController
{
    protected $appsService;
    protected $sitePageService;
    protected $userService;
    
    public function __construct(Request $request, SitePageService $sitePageService,
                                 AppsService $appsServ,
                                UserService $userServ)
    {
        parent::__construct( $request);
        $this->sitePageService = $sitePageService;
        $this->appsService = $appsServ;
        $this->userService = $userServ;
    }


    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
            'firebase_uid' => 'required'
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
   
        $input = $request->all();

   Log::info('In register, input is: '.json_encode($input));
        $input['password'] = bcrypt($input['password']);
        $sendInvitation = false; //will send welcome email
        
        $user = User::create($input);
        $success_p1 = $this->userService->register($user,'user', $sendInvitation);

        $success_p2 = $this->userService->buildConfigReturn($user, $this->appsService, $this->site);

        $success = array_merge($success_p1,$success_p2);
        $success['ShowIntro'] = 'SHOW';
   Log::info('register returning: '.json_encode($success));
        return $this->sendResponse($success, 'User registered successfully.');
    }

    /**
     * Firebase was used to authenticate this user, so we are recording that they logged in
     * and maintaining some user related data
     */
    public function record_login(Request $request)
    {
        $email= $request->input('email');
        $password= $request->input('password');
        $push_token=  $request->input('pn_token');
        
        $firebase_uid = $request->input('firebase_uid');
            
        $user = null;
        if (isset($firebase_uid ))
        {
            $user = User::where("firebase_uid",$firebase_uid)->first();
        }
        $user_logged_in=false;
        if(  Auth::attempt(['email' => $email, 'password' => $password]))
        {
            $user_logged_in = true;           
        }
        else
        { 
            if (null != $user)
            {
                //Log::info("set password, firebase auth approved so we will too");
                $user->password = bcrypt($password);
                $user->save();
                if (Auth::attempt(['email' => $email, 'password' => $password])) 
                { 
                    $user_logged_in = true;
                }
            }
        }
    
        if ( $user_logged_in)
        {
            $user = Auth::user(); 
            if ($user->firebase_uid <> $firebase_uid ||
            ($push_token != '' && $user->pn_token != $push_token))
            {
                if (isset($firebase_uid ))
                {
                    $user->firebase_uid = $firebase_uid;
                }
                
                if ($push_token != '' && isset($push_token))
                {
                    Log::info('saving push token for user');
                    $user->pn_token = $push_token;
                }
                $user->save();
            }
            $user = $this->setUpUser($request,$user);
            $success = $this->userService->buildConfigReturn($user, $this->appsService, $this->site);
            $success['pn_token'] = $user->pn_token;
                    
            return $this->sendResponse($success, 'User has logged in.');
        } 
        else{ 
            
            return $this->sendError('Unauthorized.', ['error'=>'Please enter a valid username and password']);
        } 
    }

    /**
     * @return \Illuminate\Http\Response
     * this is not used by the apps since we use firebase to login for those
     */
    public function login(Request $request)
    {
        $email= $request->input('email');
        $password= $request->input('password');
        Log::info('logging in: '.$email);
        if(Auth::attempt(['email' => $email, 'password' => $password])){ 
            $user = Auth::user(); 
            $user = $this->setUpUser($request,$user);

            $success = $this->userService->buildConfigReturn($user, $this->appsService, $this->site);

            return $this->sendResponse($success, 'User logged in successfully.');
        } 
        else{ 
            return $this->sendError('Unauthorized.', ['error'=>'Please enter a valid username and password']);
        } 
    }

    public function logout(Request $request)
    {
        $this->unsetAccessTokenCookie();
        return $this->sendResponse('', 'User logged out successfully.');
      
    }


    public function saveEnhancedProfile(Request $request)
    {
        //goes in here. as described in the notes for Aug 27
        $user = $this->userService->saveUser($request);

        $success = $this->userService->buildConfigReturn($user, $this->appsService, $this->site);
        $success['ShowIntro'] = 'DONE';
   Log::info('save enhanced profile returning: '.json_encode($success));
        return $this->sendResponse($success, 'User registered successfully.');
    }

    public function saveUser(Request $request)
    {
        return $this->saveEnhancedProfile($request);
    }

    public function getAppSettings($apptoken,Request $request)
    {
        
        $user = $this->setUpUser($request,null);

       try {
            if (!isset($user))
            {
                $this->sendToUnauthorized();
            }
            $app_data = $this->userService->buildConfigReturn($user, $this->appsService, $this->site);
            
            return $this->sendResponse($app_data, 'Successfully refreshed app data');
        } catch (\Throwable $e) {
            Log::info($e);
        }
    }
    
    private function setUpUser($request,$user)
    {
        $accessToken  = $request->header(config('constants.AUTHORIZATION_'));
        $accessToken = str_replace("Bearer ","",$accessToken);
    
        if (!isset($accessToken) && isset($_COOKIE[config('constants.AUTHORIZATION_')]))
        {
            $accessToken = $_COOKIE[config('constants.AUTHORIZATION_')];
        }
        else
        if (!isset($accessToken) && $user != null) 
        {

            $accessToken = $request->user()->createToken(config('app.name'))->accessToken->token;

Log::info('accesstoken empty but user is not. new access token: '.$accessToken);
        }
        if (isset($accessToken))
        {
Log::info('in setUpUser -  accesstoken: '.$accessToken);
            $this->setAccessTokenCookie($accessToken);
            if ($user == null)
            {
                $user = User::getUserByAccessToken($accessToken);
            }

            if ($user != null) 
            {
                \Auth::login($user); 
            }
        }
       return $user;
    }
 
    public function uploadProfileImageApi(Request $request)
    {
        $user = $this->setUpUser($request,null);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
        ]);
        if($request->hasfile('image'))
        {
            $file = $request->file('image');
            $imageName=time().$file->getClientOriginalName();
            $filePath = 'Prasso/-user-photos/photos-'.$user->id.'/'. $imageName;
            Storage::disk('s3')->put($filePath, file_get_contents($file));
            $usr = User::where('email',$user->email)->first();
            $usr->profile_photo_path = $filePath;
            $usr->save();
            
            $success['photoURL'] = $user->getProfilePhoto();
        return $this->sendResponse($success, 'Photo updated successfully.');
        //return back()->with('success','The image has been uploaded')->with('user',$usr);
        }   
    }

    private function sendToUnauthorized()
    {
        $response['message'] = trans('messages.invalid_token');
        $response['success']= false;
        $response['status_code'] = \Symfony\Component\HttpFoundation\Response::HTTP_UNAUTHORIZED;
        return $this->sendError('Unauthorized.', ['error'=>'Please login again.'], 400);

    }
       
    /**
    * function is used to accessToken email cookie to browser
    */
    protected function unsetAccessTokenCookie()
    {
        setcookie(config('constants.ACCESSTOKEN_'), '', time() - 3600, "/"); 
    }

    /**
     * function is used to set accessToken cookie to browser
     */
    protected function setAccessTokenCookie($accessToken)
    {
        setcookie(config('constants.ACCESSTOKEN_'), $accessToken, time() + (86400 * 30), "/");
     }
}
