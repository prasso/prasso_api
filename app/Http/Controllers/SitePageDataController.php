<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Models\SitePageData;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SitePageDataController extends BaseController
{
    protected $userService;

    public function __construct(Request $request, UserService $userServ)
    {
        parent::__construct( $request);
        $this->userService = $userServ;
    }


    public function destroy(Request $request,$pageid,$id)
    {
        $user = Auth::user() ?? null;   
        if ($user == null){
            \App\Http\Middleware\UserPageAccess::authorizeUser($request);
            if ($user == null){
                Auth::logout();
                session()->flash('status',config('constants.LOGIN_AGAIN'));
                return redirect('/login');
            }
        }
        //make sure user has access to edit
        if (!$this->userService->isUserOnTeam($user))
        {
            info('user is not on team to edit site page data '.$user->id);
            abort(404);
        }
        //get record id'd by dataid
        $data = SitePageData::where('id',$id)
                ->firstOr(function () {
                    return null;
                });

        $data->delete();
        $success['message'] = 'Data deleted successfully!';

        return $this->sendResponse($success, 'Data Deleted.');
    }
}