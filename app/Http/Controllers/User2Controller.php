<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class User2Controller extends BaseController
{

    public function __construct(Request $request)
    {
        parent::__construct( $request);
        $this->middleware('instructorusergroup');
    }

    public function update_user($userid)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            return redirect('/dashboard');
        }
        $usr = User::where('id',$userid)->first();

View::share('site',$this->site);
        return view('profile.update-user-form')->with('user',$usr);
    }

    public function prasso_profile()
    {
        $user = Auth::user(); 
        $usr = User::where('email',$user->email)->first();
        return view('profile.prasso-profile')->with('user',$usr);
    }

    public function uploadProfileImage(Request $request)
    { 
        info('saving a profile image. ');
        $user = Auth::user(); 

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
        ]);
        if($request->hasfile('image'))
        {
            $file = $request->file('image');
            $imageName=time().$file->getClientOriginalName();
            $filePath = config('constants.PROFILE_PHOTO_PATH') .'photos-'.$user->id.'/'. $imageName;
            Storage::disk('s3')->put($filePath, file_get_contents($file));
            $usr = User::where('email',$user->email)->first();
            $usr->profile_photo_path = $filePath;
            $usr->save();
        return back()->with('success','The image has been uploaded')->with('user',$usr);
        }   
    }


}
