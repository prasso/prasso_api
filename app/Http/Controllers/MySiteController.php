<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use App\Services\UserService;
use App\Models\Instructor;
use Auth;

class MySiteController extends BaseController
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
     *      livestreams are a paid feature and can be added to the site if the site has a paid subscription
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function editMySite(Request $request)
    {
        if (!Controller::userOkToViewPageByHost($this->userService))
        {
            return redirect('/login');
        }
        $mysite = Controller::getClientFromHost();
        $user = Auth::user();
        
       
        return view('sites.my-site-editor')->with('site', $mysite)->with('user', $user)->with('team', $user->currentTeam);
    }   


}
