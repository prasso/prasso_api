<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SitePageController extends Controller
{
    /**
     * return welcome page
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('welcome');
    }
}
