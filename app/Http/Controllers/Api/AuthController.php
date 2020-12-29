<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Log;
use App\Providers\AppServiceProvider;
use Illuminate\Database\Eloquent\JsonEncodingException;
use App\Actions\Fortify\CreateNewUser;

class AuthController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        log::info($request->all());
        $validator = Validator::make($request->all(), [
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
        $input['password'] = bcrypt($input['password']);
        
        $user = User::create($input);
        $user->ownedTeams()->save(Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0]."'s Team",
            'personal_team' => true,
        ]));
        $success = $this->buildConfigReturn($user);

        return $this->sendResponse($success, 'User registered successfully.');
    }

    /**
     * Firebase was used to authenticate this user, so we are recording that they logged in
     * and maintaining some user related data
     */
    public function record_login(Request $request)
    {
        Log::info($request); //debug
        $email= $request->input('email');
        $password= $request->input('password');
        $push_token= $request->input('pn_token');
        
        $firebase_uid = $request->input('firebase_uid');
            
        $user = null;
        if (isset($firebase_uid ))
        {
            $user = User::where("firebase_uid",$firebase_uid)->first();

        }
        if( ! Auth::attempt(['email' => $email, 'password' => $password])){ 
            if (null != $user)
            {
                Log::info("set password, firebase auth approved so we will too");
            
                $user->password = bcrypt($password);
                $user->save();
            }
        }
        if( Auth::attempt(['email' => $email, 'password' => $password])){ 
        
            $user = Auth::user(); 
            if ($user->firebase_uid <> $firebase_uid || $user->push_token != $push_token)
            {
                if (isset($firebase_uid ))
                {
                    $user->firebase_uid = $firebase_uid;
                }
                if (isset($push_token))
                {
                    $user->pn_token = $push_token;
                }

                $user->save();
            }
            $success = $this->buildConfigReturn($user);

            return $this->sendResponse($success, 'User has logged in.');
        } 
        else{ 
            
            return $this->sendError('Unauthorized.', ['error'=>'Please enter a valid username and password']);
        } 
    }
    /**
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $email= $request->input('email');
        $password= $request->input('password');
        $app_token=$request->input('app_token'); //identifies which app

        if(Auth::attempt(['email' => $email, 'password' => $password])){ 
            $user = Auth::user(); 
            $success = $this->buildConfigReturn($user,$app_token);

            return $this->sendResponse($success, 'User logged in successfully.');
        } 
        else{ 
            return $this->sendError('Unauthorized.', ['error'=>'Please enter a valid username and password']);
        } 
    }

    public function saveUser(User $user, Request $request)
    {
        Log::info(json_encode($user));
        $user->save();
    }

    public function getAppSettings($apptoken,Request $request)
    {
        $user = $this->setUpUser($request);

       try {
            if (!isset($user))
            {
                $this->sendToUnauthorized();
            }
            $app_data = $this->buildConfigReturn($user);
            
            return $this->sendResponse($app_data, 'Successfully refreshed app data');
        } catch (\Throwable $e) {
            Log::info($e);
        }
    }
    /**
     * Consolidate code used in multiple places
     */
    private function buildConfigReturn($user)
    {

        $success = [];
        $success['token'] =  json_encode($user->createToken(config('app.name'))->accessToken->token); 
        $success['name'] =  $user->name;
        $success['uid'] = $user->id;
        $success['email'] = $user->email;
        $success['photoURL'] = $user->profile_photo_url;
    
        $app_data = AppServiceProvider::getAppSettingsByUser($user);
        $success['app_data'] = $app_data; //configuration for setting up the app is here
        return $success;
    }

    private function setUpUser($request)
    {
        Log::info(json_encode($request)); //debug
        
        $accessToken  = $request->header('Authorization');
        $accessToken = str_replace("Bearer","",$accessToken);
    
        if (!isset($accessToken) && isset($_COOKIE['Authorization']))
        {
            $accessToken = $_COOKIE['Authorization'];
            $this->setAccessTokenCookie($accessToken);
        }
        if (!isset($accessToken)) {
           return $this->sendToUnauthorized();
        }

        $user = User::getUserByAccessToken($accessToken);

        if ($user == null) {
            $this->unsetAccessTokenCookie();
            return $this->sendToUnauthorized();
          }
        
        \Auth::login($user);
       return $user;
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
        setcookie('accessToken', '', time() - 3600, "/"); 
    }

    /**
     * function is used to set accessToken cookie to browser
     */
    protected function setAccessTokenCookie($accessToken)
    {
        setcookie('accessToken', $accessToken, time() + (86400 * 30), "/");
    }
}
