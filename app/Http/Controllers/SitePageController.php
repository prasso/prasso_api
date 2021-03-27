<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\SitePageService;
use App\Models\SitePages;
use App\Models\Site;

class SitePageController extends Controller
{
    protected $site;
    protected $sitePageService;
    
    public function __construct(Site $site, SitePageService $sitePageService)
    {
        $this->site = $site;
        $this->sitePageService = $sitePageService;
    }
    /**
     * return welcome page
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $welcomepage = SitePages::where('fk_site_id',$this->site->id)->where('section','Welcome')->first();

        if ($welcomepage == null)
        {
            return view('welcome');
        }
        return view('sitepage.masterpage')
            ->with('sitePage',$welcomepage);
    }

     /**
     * Show the app edit form 
     *
     * @return \Illuminate\Http\Response
     */
    public function editSites()
    {
        return view('sitepage.view-site-pages');
    }

    public function visualEditor($pageid)
    {
        $pageToEdit = SitePages::where('id',$pageid)->first();
        return view('sitepage.grapes')->with('sitePage', $pageToEdit);
    }

    public function saveSitePage(Request $request)
    {
        $this->sitePageService->saveSitePage($request);
        return redirect()->back();
    }
}
