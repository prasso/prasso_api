<?php

namespace App\Http\Livewire;
use App\Models\User;
use App\Models\Site;
use App\Models\Apps;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\site_needs_dns;


use Livewire\Component;
 /**
  * A wizard component that allows the user to register if not already registered,
  * create a new site and app associated with the site.
  * uses pre-existing app and site livewire components
  * 
  */
class NewSiteAndApp extends Component
{
    public $business_type;

    public $newApp;

    public $newSite;
    public $site_name; //
    public $description; //
    public $host; //
    public $main_color; //
    public $logo_image; //

    public $database;
    public $favicon;
    public $team;
    public $current_user;
    public $currentStep = 1;
    public $step1, $step2, $step3, $step4 = false;

    protected $rules = [
            'site_name' => 'required|string|max:200',
            'host' => 'required|string|max:200',
            'main_color' => 'required|string|min:6',
            'business_type' => 'required',
            'description' => 'required',
            'logo_image' => 'required',
           ];
        
    public function mount(User $user, Team $team, Request $request)
    {
        //does this user have an admin role?
        $this->current_user = $user;
        $this->team = $team;
        $this->newSite = new Site();
        $this->newApp = new Apps();
        $this->step1 = true;
        $this->newSite->database = 'prasso';
        $this->newSite->favicon = 'favicon.ico';
    }

    public function render()
    {
        return view('livewire.new-site-and-app');
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }    

    public function wizardProgress($direction){
        if ($direction == 'NEXT'){
            $this->currentStep++;
        }
        if ($direction == 'PREV'){
            $this->currentStep--;
        }

        switch($this->currentStep){
            case 1:
                $this->step1 = true;
                $this->step2 = false;
                $this->step3 = false;
                $this->step4 = false;
                break;
            case 2:
                $this->step1 = false;
                $this->step2 = true;
                $this->step3 = false;
                $this->step4 = false;
                break;
            case 3:
                $this->step1 = false;
                $this->step2 = false;
                $this->step3 = true;
                $this->step4 = false;

                break;
            case 4:

        $this->host =  $this->host.'.prasso.io'; //
        $this->database = 'prasso';
        $this->favicon = 'favicon.ico';

                $this->step1 = false;
                $this->step2 = false;
                $this->step3 = false;
                $this->step4 = true;
                break;

        }
    }

    public function createSiteAndApp()
    {
        $validatedData = $this->validate();
        //the code will not continue if it is not validated

        /**put the data into newSite and newApp*/
        $this->newApp->team_id = $this->team->id;
        $this->newApp->appicon = $this->logo_image;
        $this->newApp->app_name = $this->site_name;
        $this->newApp->page_title = $this->site_name;
        $this->newApp->page_url = $this->site_name;
        $this->newApp->sort_order = '1';

        $this->newSite->site_name = $this->site_name; //
        $this->newSite->description = $this->description; //
        $this->newSite->host =  $this->host; //
        $this->newSite->main_color = $this->main_color; //
        $this->newSite->logo_image = $this->logo_image; //
        $this->newSite->database = 'prasso';
        $this->newSite->favicon = 'favicon.ico';

        //convert the objects to arrays
        $newSite = $this->newSite->toArray();
        $newApp = $this->newApp->toArray();

        $this->newSite::create($newSite);
        $this->newApp::create($newApp);

        //notify me that I need to finish this setup with DNS record
        Log::info('send new site and app notification mail ');

        Mail::to('info@prasso.io', 'Prasso Admin')->send(new site_needs_dns($this));
 
                
        
        $this->currentStep = 1;
        session()->flash('message', 'Site created successfully. Please wait for DNS setup to complete.');
        redirect()->route('sites.show')
            ->with('success', 'Site created successfully. Please wait for DNS setup to complete.');
    }
}