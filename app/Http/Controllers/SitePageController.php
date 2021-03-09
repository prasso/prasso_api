<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SitePages;

class SitePageController extends Controller
{
    /**
     * return welcome page
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $welcomepage = SitePages::where('section','Welcome')->first();
        if ($welcomepage == null)
        {
            return view('Welcome');
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
}
