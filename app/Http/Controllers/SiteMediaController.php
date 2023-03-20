<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use App\Services\MediaService;
use App\Models\SiteMedia;
use Auth;

class SiteMediaController extends Controller
{

    protected $mediaService;

    public function __construct(Request $request, MediaService $mediaServ)
    {
        parent::__construct( $request);
        $this->middleware('instructorusergroup');

        $this->mediaService = $mediaServ;
    }

    public function siteMediaEdit(Request $request) {
        
        $edit_id=$request['sitemediaid'];
   
        if ($edit_id == 0){
            session()->flash('message','No media selected' );
            return redirect()->back();
        }
        $site_media = SiteMedia::findOrFail($edit_id);
        
        return view('admin.edit-site-media')
            ->with('media', $site_media);

        
    }
    /**
     * Show the form for establishing settings that are used to move this
     * temporary livestream folder to a production presentation folder for permanent
     * storage
     *
     * @return \Illuminate\Http\Response
     */
    public function siteMediaCreate(Request $request){

        $site_media = $this->mediaService->getSiteMediaDetailsFromRequest($request);
        if ($site_media == null){
            return redirect()->back();
        }
        return view('admin.edit-site-media')
            ->with('media', $site_media);
    }
}