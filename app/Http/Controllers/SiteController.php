<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\faqs_storage;
use Auth;
use App\Http\Requests\SiteRequest;

class SiteController extends BaseController
{
    public function __construct(SiteRequest $request)
    {
        parent::__construct( $request);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $user = Auth::user() ?? null;   
        if ($user == null || $user->currentTeam == null){
            Auth::logout();
            session()->flash('status',config('constants.LOGIN_AGAIN'));
            return redirect('/login');
        }
        $sites = Site::latest()->paginate(15);
        $team = $user->currentTeam;
        $team_selection = $team->pluck('name','id');
        
        return view('sites.show', compact('sites'))
            ->with('i', (request()->input('page', 1) - 1) * 5)
            ->with('team_selection', $team_selection);
    }

    /**
     * FAQ show what we have
     *
     */
    public function seeFaqs()
    {
        $faqs = faqs_storage::latest()->paginate(15);

        return view('sites.faqs', compact('faqs'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * FAQ question,
     * eventually - send the message via Twilio to the customer support folk
     * atm txt to me
     *
     */
    public function processQuestion(Request $request, Site $site)
    {
        $this->validate($request, [
            'question' => 'required'
        ]);
        $this->serverKey = config('app.firebase_server_key');
        $question = $request->question;
        $email = $request->email;

        $admin_user = \App\Models\User::where('email','bcp@faxt.com')->first();

        $data = [
            "to" => $admin_user->pn_token,
            "notification" =>
                [
                    "title" => 'Prasso FAQ Request',
                    "body" => $question.' and Reply To: '.$email,
                    "icon" => url($site->logo_image)
                ],
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $this->serverKey,
            'Content-Type: application/json',
        ];
   
        $url='https://fcm.googleapis.com/fcm/send';
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization'=> 'key='. $this->serverKey,
        ])->post($url, $data);

        return redirect('/page/faqs')->with('message', 'Your question was sent.'); 

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $team = Auth::user()->currentTeam;
        $team_selection = $team->pluck('name','id');
        return view('sites.create-or-edit')
            ->with('team_selection', $team_selection);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SiteRequest $request)
    {
        $request->validated();

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
    public function update(SiteRequest $request, Site $site)
    {
        $request->validated();
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
