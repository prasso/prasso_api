<?php

namespace App\Livewire;
use App\Models\User;
use App\Models\Site;
use App\Models\Apps;
use App\Models\Team;
use App\Models\LivestreamSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\new_site_notification;
use Auth;
use Illuminate\Support\Facades\Artisan;
use Livewire\WithFileUploads;
use App\Http\Requests\SiteRequest;

use Illuminate\Support\Facades\Validator;


use Livewire\Component;
 /**
  * A wizard component that allows the user to register if not already registered,
  * create a new site and app associated with the site.
  * uses pre-existing app and site livewire components
  * 
  */
class NewSiteAndApp extends Component
{
    use WithFileUploads;

    public $business_type;
    public $newApp;
    public $newSite;
    public $site_name; //
    public $description; //
    public $host; //
    public $main_color; //
    public $image_folder; //
    public $logo_image; //
    public $supports_registration;//
    public $subteams_enabled; //
    public $does_livestreaming; //
    public $app_specific_js;//
    public $app_specific_css;//
    // for super admin setting team id
    public $team_id;

    public $database;
    public $favicon;
    public $team;
    public $current_user;
    public $currentStep = 1;
    public $step1, $step2, $step3, $step4 = false;
    public $team_selection;


    public $photo;
        
    public function mount(User $user, Team $team, $team_selection, Request $request)
    {

        $this->team_selection = $team_selection;
        $this->team_id = $team->id;
        
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

    private function lowercaseWithoutSpacesSiteName(){
        return strtolower(str_replace(" ", "", $this->site_name));
    }

    public function updated($propertyName)
    {
        if ($propertyName == 'site_name' && $this->site_name != '' && $this->host == '') {
            $this->host = $this->lowercaseWithoutSpacesSiteName();
        }

        if ($propertyName == 'host') $this->checkhost();

        if ($propertyName == 'site_name' && $this->site_name != '' && $this->image_folder == '') {
            $this->image_folder = $this->lowercaseWithoutSpacesSiteName().'/';
        }

        $siteRequest = new SiteRequest();
        $this->resetErrorBag($propertyName);
     
        $this->validateOnly($propertyName, $siteRequest->rules());
         

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
                $this->checkhost();
                $this->database = 'prasso';
                $this->favicon = 'favicon.ico';

                $this->step1 = false;
                $this->step2 = false;
                $this->step3 = false;
                $this->step4 = true;
                break;

        }
    }
    private function checkhost(){
        $this->host = str_replace(' ', '', $this->host);
        if ( !str_ends_with($this->host,  'prasso.io')){
             $this->host =  $this->host.'.prasso.io';
        }
    }

    public function createSiteAndApp()
    {
        $siteRequest = new SiteRequest();
        $this->validate($siteRequest->rules());

        $this->newSite->site_name = $this->site_name; //
        $this->newSite->description = $this->description; //
        $this->newSite->host =  $this->host; //
        $this->newSite->main_color = $this->main_color; //
        $this->newSite->logo_image = $this->logo_image??'pending upload'; //
        $this->newSite->database = 'prasso';
        $this->newSite->favicon = 'favicon.ico';
        $this->newSite->supports_registration = $this->supports_registration;//
        $this->newSite->subteams_enabled = $this->subteams_enabled; //
        $this->newSite->app_specific_css = ".teambutton {color:#f1f1f1;background-color: {$this->main_color};}";
        $this->newSite->image_folder = $this->image_folder; //
        $this->newSite->app_specific_js = $this->app_specific_js;
        $this->newSite->app_specific_css = $this->app_specific_css;
        

        $newSite = $this->newSite->toArray();
        $site = $this->newSite::create($newSite);

        $this->current_user = Auth::user();

        $team = $site->updateTeam($this->team_id);

        //upload the image if present
        if ($this->photo){
            $this->logo_image = $site->uploadImage($this->photo);
            $site->logo_image = $this->logo_image;
            $site->save();
        }
        if ($this->does_livestreaming){
            LivestreamSettings::addOrUpdate($site);
        }

        /**when the app is created - team_id should be the team the site belongs to
	    and site_id should have been the id of the site just created
        ut the data into newSite and newApp*/
        $this->newApp->team_id = $team->id;
        $this->newApp->site_id = $site->id;
        $this->newApp->appicon = $this->logo_image;
        $this->newApp->app_name = $this->site_name;
        $this->newApp->page_title = $this->site_name;
        $this->newApp->page_url = $this->site_name;
        $this->newApp->sort_order = '1';

        $newApp = $this->newApp->toArray();
        $this->newApp::create($newApp);

        Artisan::call("dns:setup", [
            'site' => $this->host
        ]);
        //notify me new site and app
        try{
            Mail::to('info@prasso.io', 'Prasso Admin')->send(new new_site_notification($this));
        }catch(\Throwable $e){
            Log::info("Error sending email: {$site->host}");
            Log::info($e);
        }
                
        //add the two site pages, welcome and dashboard
        $site->addDefaultSitePages();

        $this->currentStep = 1;
        session()->flash('message', 'Site created successfully. Please wait for DNS setup to complete.');
        redirect()->route('dashboard')
            ->with('success', 'Site created successfully. Please wait for DNS setup to complete.');
    }
}
