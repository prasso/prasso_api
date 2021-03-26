<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\SitePages;
use App\Models\Site;

class SitePageController extends Controller
{
    protected $site;
    
    public function __construct(Site $site)
    {
        $this->site = $site;
    }
    /**
     * return welcome page
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $welcomepage = SitePages::where('fk_site_table',$this->site->id)->where('section','Welcome')->first();

        if ($welcomepage == null)
        {
            return view('welcome');
        }
        return view('sitepage.masterpage')
            ->with('content',$welcomepage);
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
        return view('sitepage.grapes')->with('content', $pageToEdit);
    }
}
