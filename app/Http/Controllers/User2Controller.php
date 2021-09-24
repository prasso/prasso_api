<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class User2Controller extends BaseController
{

    public function __construct(Request $request)
    {
        parent::__construct( $request);
        $this->middleware('instructorusergroup');
    }

    public function Prasso_profile()
    {
        $user = Auth::user(); 
        $usr = User::where('email',$user->email)->first();
        return view('profile.Prasso-profile')->with('user',$usr);
    }

    public function uploadProfileImage(Request $request)
    {
        
        $user = Auth::user(); 

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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
        return back()->with('success','The image has been uploaded')->with('user',$usr);
        }   
    }

    public function setupProfile(Request $request)
    {

    }

}
