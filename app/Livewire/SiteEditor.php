<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\LivestreamSettings;
use App\Models\Team;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\SiteRequest;
use App\Models\Site;
use App\Models\Apps;
use App\Models\Tabs;
use App\Models\Stripe;
use Auth;
use Livewire\WithFileUploads;
use App\Services\AppSyncService;

class SiteEditor extends Component
{
    use WithFileUploads;

    protected $listeners = ['deleteSite'];


    public $sites, $site_id,$site_name,$description, $host,$main_color,$logo_image, 
            $database, $favicon, $supports_registration, $subteams_enabled, $app_specific_js, $app_specific_css,
            $does_livestreaming,$https_host, $image_folder,$invitation_only;
    public $stripe_key, $stripe_secret;

    public $sitePages;
    public $current_user;
    public $isOpen = 0;

    public $photo;
    public $showSyncDialog = false;
    public $selectedPages = [];
    public $team_selection;
    public $team_id;

    protected $appSyncService;
    
    public function mount(User $user, AppSyncService $appSyncService, $team_selection)
    {
        $this->team_selection = $team_selection;

        $this->appSyncService = $appSyncService;
        $this->current_user = $user;
    }

    public function render()
    {
        $this->sites = Site::all();
        return view('livewire.site-editor')
            ->with('team_selection', $this->team_selection);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }
  
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function openModal()
    {
        $this->isOpen = true;
    }
  
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function closeModal()
    {
        $this->isOpen = false;
    }
  
    public function showTheSyncDialog($id)
    {
        $site = Site::findOrFail($id);
        
        $this->site_id = $id;
        $this->site_name = $site->site_name;
        if ($site->teams->count() > 0)
        {$this->team_id = $site->teams[0]->id;}

        $this->description = $site->description;
        $this->host = $site->host;
        $this->main_color = $site->main_color;
        $this->logo_image = $site->logo_image;
        $this->database = $site->database;
        $this->favicon = $site->favicon;
        $this->supports_registration = $site->supports_registration;
        $this->subteams_enabled = $site->subteams_enabled;
        $this->does_livestreaming = $site->livestream_settings()->exists();
        $this->invitation_only = $site->invitation_only;
        
        $this->app_specific_js = $site->app_specific_js;
        $this->app_specific_css = $site->app_specific_css;
        $this->image_folder = $site->image_folder;

        // Initialize stripe_key and stripe_secret if the stripe relationship has data
        if ($site->stripe) {
            $this->stripe_key = $site->stripe->key;
            $this->stripe_secret = $site->stripe->secret;
        } else {
            $this->stripe_key = '';
            $this->stripe_secret = '';
        }

        $this->sitePages = $site->sitePages;
        $this->showSyncDialog = true;
    }

    public function hideSyncDialog()
    {
        $this->showSyncDialog = false;
        if ($this->selectedPages != null)
        {
            $this->syncAppToSite();
        }
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    private function resetInputFields()
    {
        $this->site_name = '';
        $this->description = '';
        $this->host = '';
        $this->main_color = '';
        $this->logo_image = '';
        $this->favicon = '';
        $this->supports_registration = '';
        $this->subteams_enabled = '';
        $this->app_specific_js = '';
        $this->app_specific_css = '';
        $this->does_livestreaming = '';
        $this->image_folder = '';
        $this->invitation_only = '';
        $this->stripe_key = '';
        $this->stripe_secret = '';
        $this->photo = null;
    }
     
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function store()
    {

        if (empty($this->database))
        {
            $this->database = 'prasso';
        }
        $siteRequest = new SiteRequest($this->site_id);
        $this->validate($siteRequest->rules());

        $newsite=false;
        if (empty($this->site_id))
        {
            $this->site_id = 0;
            $newsite=true;
        }
        
        $site = Site::updateOrCreate(['id' => $this->site_id], [
            'site_name' => $this->site_name,
            'description' => $this->description,
            'host' => $this->host,
            'main_color' => $this->main_color,
            'logo_image' => $this->logo_image,
            'database' => $this->database,
            'favicon' => $this->favicon,
            'supports_registration' => $this->supports_registration,
            'invitation_only' => $this->invitation_only,
            'subteams_enabled' => $this->subteams_enabled,
            'app_specific_js' => $this->app_specific_js,
            'app_specific_css' => $this->app_specific_css,
            'image_folder' => $this->image_folder,
        ]);
        
        $this->site_id = $site->id;

        // Save to stripe table only if stripe_key and stripe_secret are not empty
        if (!empty($this->stripe_key) && !empty($this->stripe_secret)) {
            Stripe::updateOrCreate(
                ['site_id' => $this->site_id],
                ['key' => $this->stripe_key, 'secret' => $this->stripe_secret]
            );
        }

        $this->current_user = Auth::user();
        // new sites need a new team for their users
        if ($newsite)
        {
            $this->site_id = $site->id;
            $site->createTeam($this->current_user->id);
        }
        else{
            $site->updateTeam($this->team_id);
        }

        //upload the image if present
        if ($this->photo){
            $this->logo_image = $site->uploadImage($this->photo);
            $site->logo_image = $this->logo_image;
            $site->save();
        }
        if ($this->does_livestreaming){
            LivestreamSettings::addOrUpdate($site);
        }
        else{
            LivestreamSettings::remove($site->id);
        }
        session()->flash('message', 
            $this->site_id ? 'Site Updated Successfully.' : 'Site Created Successfully.');
  
        $this->closeModal();
        
        $this->resetInputFields();
    }

    public function updated($propertyName)
    {
       
        if ($propertyName == 'site_name' && $this->site_name != '' && $this->image_folder == '') {
            $words = explode(" ", $this->site_name);
            $first_word = $words[0];
            $this->image_folder = $first_word.'/';
        }
        $this->resetErrorBag($propertyName);
     
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function edit($id)
    {
        $site = Site::findOrFail($id);
        $this->site_id = $id;
        $this->site_name = $site->site_name;
        if ($site->teams->count() > 0)
        {$this->team_id = $site->teams[0]->id;}

        $this->description = $site->description;
        $this->host = $site->host;
        $this->main_color = $site->main_color;
        $this->logo_image = $site->logo_image;
        $this->database = $site->database;
        $this->favicon = $site->favicon;
        $this->supports_registration = $site->supports_registration;
        $this->subteams_enabled = $site->subteams_enabled;
        $this->does_livestreaming = $site->livestream_settings()->exists();
        $this->invitation_only = $site->invitation_only;
        
        $this->app_specific_js = $site->app_specific_js;
        $this->app_specific_css = $site->app_specific_css;
        $this->image_folder = $site->image_folder;

        if ($site->stripe) {
            $this->stripe_key = $site->stripe->key;
            $this->stripe_secret = $site->stripe->secret;
        } else {
            $this->stripe_key = '';
            $this->stripe_secret = '';
        }      

        $this->openModal();
    }
    
    public function syncAppToSite()
    {
        $site = Site::findOrFail($this->site_id);
        $app = $site->app;

        if ($this->appSyncService == null){

            $this->appSyncService = new AppSyncService();
        }
        $this->appSyncService->syncSelectedSitePagesToApp($site, $app, $this->selectedPages);
  
        $this->showSyncDialog = false;
    }


    public function deleteSite($id)
    {
        info('delete site received');
        $this->delete($id);
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function delete($id)
    {
        Site::find($id)->delete();
        session()->flash('message', 'Site  Deleted Successfully.');
    }

}
