<?php

namespace App\Http\Controllers;


use App\Http\Requests\SitePageTemplateRequest;
use App\Models\SitePageTemplate;

class SitePageDataTemplateController extends Controller
{
    public function index()
    {
        $templates = SitePageTemplate::all();

        return view('site-page-data-templates.index', compact('templates'));
    }  
    public function edit($id)
    {
        $template = SitePageTemplate::findOrFail($id);

        return view('site-page-data-templates.edit', compact('template'));
    }
    
    public function create()
    {
        $template =  SitePageTemplate::getDefaultBlank();
        return view('site-page-data-templates.create', compact('template'));
    }
    public function destroy($id)
    {
        $template = SitePageTemplate::findOrFail($id);

        $template->delete();

        return redirect('/site-page-data-templates');
    }
   
}
