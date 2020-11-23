<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Log;
use App\Providers\AppServiceProvider;

class AuthController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
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
        $success['token'] =  $user->createToken(config('app.name'))->accessToken;
        $success['name'] =  $user->name;
   
        return $this->sendResponse($success, 'User register successfully.');
    }

    /**
     * Firebase was used to authenticate this user, so we are recording that they logged in
     * and maintaining some user related data
     */
    public function record_login(Request $request)
    {
        $email= $request->input('email');
        $password= $request->input('password');
        $push_token= $request->input('pn_token');
        $app_token=$request->input('app_token'); //identifies which app

        $firebase_uid = $request->input('firebase_uid');
        if (isset($firebase_uid ))
        {
            $user = User::where("firebase_uid",$firebase_uid)->first();

        }
        if( ! Auth::attempt(['email' => $email, 'password' => $password])){ 
            if (null != $user)
            {
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
            $success['token'] =  json_encode($user->createToken(config('app.name'))->accessToken->token); 
            $success['name'] =  $user->name;
            $success['uid'] = $user->id;
            $success['email'] = $user->email;
            $success['photoURL'] = $user->profile_photo_url;
            $app_data = AppServiceProvider::getAppSettings($app_token);
            $success['app_data'] = $app_data; //configuration for setting up the app is here
   Log::info($success); //bobbi
            return $this->sendResponse($success, 'User login successfully.');
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
        $push_token= $request->input('token');
        $firebase_uid = $request->input('firebase_uid');

        if(Auth::attempt(['email' => $email, 'password' => $password])){ 
            $user = Auth::user(); 
            
            $success['token'] =  $user->createToken(config('app.name'))->accessToken; 
            $success['name'] =  $user->name;
            $success['uid'] = $user->id;
            $success['email'] = $user->email;
            $success['photoURL'] = $user->profile_photo_url;
   
            return $this->sendResponse($success, 'User login successfully.');
        } 
        else{ 
            return $this->sendError('Unauthorized.', ['error'=>'Please enter a valid username and password']);
        } 
    }
}
