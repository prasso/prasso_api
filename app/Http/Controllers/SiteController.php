<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SiteController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct( $request);
        $this->middleware('super_admin');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        $sites = Site::latest()->paginate(5);

        return view('sites.show', compact('sites'))
            
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('sites.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'host' => 'required',
            'logo_image' => 'required',
            'main_color' => 'required',
            'database' => 'required' ,
            'favicon' => 'required'
        ]);

        Site::create($request->all());

        return redirect()->route('sites.index')
            ->with('success', 'Site created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function show(Site $site)
    {
        return view('sites.show', compact('Site'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Site $site)
    {
        $request->validate([
            'host' => 'required',
            'logo_image' => 'required',
            'main_color' => 'required',
            'database' => 'required',
            'favicon' => 'required'
        ]);
        $site->update($request->all());

        return redirect()->route('sites.index')
            ->with('success', 'Site updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function destroy(Site $site)
    {
        $site->delete();

        return redirect()->route('sites.index')
            ->with('success', 'Site deleted successfully');
    }
}
