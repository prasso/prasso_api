<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use App\Services\UserService;
use App\Models\Instructor;
use Auth;

class MySiteController extends Controller
{

    protected $userService;

    public function __construct(Request $request,UserService $userServ)
    {
        parent::__construct( $request);
        $this->middleware('instructorusergroup');

        $this->userService = $userServ;
    }


 

    /**
     * Show the form for editing the logged in user's site.
     * check first these things:
     *   does the current user have access to this site?
     *      a user can edit a site if they belong to the sites team and have instructor permissions
     *
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function editMySite(Request $request)
    {
        $mysite = Controller::getClientFromHost();
        if ($mysite == null)
        {
           session()->flash('status','Unknown site, if just created wait a bit for the Internet to realize it exists.');
           return redirect('/login');
        }

        if (!$this->userService->isUserOnTeam(Auth::user()))
        {
            Auth::logout();
            session()->flash('status','You are not a member of this site.');
            return redirect('/login');
        }
       
        return view('sites.my-site-editor')->with('site', $mysite)->with('user', Auth::user())->with('team', Auth::user()->currentTeam);
    }   


}
