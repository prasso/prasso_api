<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use App\Services\UserService;
use App\Services\MediaService;
use App\Models\Site;
use Auth;

class AdminController extends BaseController
{

    protected $userService;
    protected $mediaService;

    public function __construct(Request $request,UserService $userServ, MediaService $mediaServ)
    {
        parent::__construct( $request);
        $this->middleware('instructorusergroup');

        $this->userService = $userServ;
        $this->mediaService = $mediaServ;
    }


 

    /**
     * This is the maintenance page for the livestream
     * S3 storage where the livestreams have been stored as they are created is temporary
     * get a list of the folders in the temporary storage and display them
     * for the user to move to permanent storage. This will store them in a table
     * for the recent videos page .
     * first one is FBC Recent Sermons.
     * do this in a way that allows for this livestream functionality to be used by any site
     * selected as an option and a paid feature.
     *
     * the middleware will check that the user is instructor level and on the team for the site
     * @return \Illuminate\Http\Response
     */
    public function livestreamMtce(Request $request)
    {
        $user = \Auth::user();

        if (!$user->isSuperAdmin())
        {
            return redirect('/login');
        }

        $site = $this->mediaService->getSiteFromRequest($request);
        if ($site->livestream_settings == null)
        {
            session()->flash('message','Site does not have livestream settings' );
            return redirect()->back();
        }
        $livestreams = $this->mediaService->getUnQueuedLivestreams($site);
        return view('admin.admin-livestream-mtce')
            ->with('site', $site) 
            ->with('user', $user)
            ->with('livestreams', $livestreams);
    }   


}
