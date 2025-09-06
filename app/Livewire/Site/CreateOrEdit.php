<?php

namespace App\Livewire\Site;

use Livewire\Component;
use App\Models\Site;
use App\Models\User;
use App\Models\Team;
use App\Models\Stripe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\SiteRequest;
use Livewire\WithFileUploads;

class CreateOrEdit extends Component
{
    use WithFileUploads;

    public $site_id;
    public $site_name; //
    public $description; //
    public $host; //
    public $main_color; //
    public $logo_image; //
    public $supports_registration; //
    public $subteams_enabled; //
    public $does_livestreaming; //
    public $invitation_only; //
    public $github_repository; //
    
    public $database;
    public $favicon;
    public $app_specific_js;
    public $app_specific_css;
    public $current_user;
    public $team;
    public $show_modal = true;
    public $image_folder;

    public $photo;
    public $team_selection;
    public $team_id;
    public $site;
    public $stripe_key, $stripe_secret;


    public function mount(Site $site, User $user, Team $team, $show_modal, $team_selection)
    {
        $this->team_selection = $team_selection;

        if ($site == null) return;
        $this->site = $site;
        $this->show_modal = $show_modal;

        //does this user have an admin role?
        $this->current_user = $user;
        $this->team = $team;
        $this->team_id = $team->id;
        $this->site_id = $site->id;
        $this->site_name = $site->site_name;
        $this->description = $site->description;
        $this->host = $site->host;
        $this->main_color = $site->main_color;
        $this->logo_image = $site->logo_image;
        $this->supports_registration = $site->supports_registration;
        $this->subteams_enabled = $site->subteams_enabled;
        $this->does_livestreaming = $site->livestream_settings()->exists();
        $this->invitation_only = $site->invitation_only;
        $this->github_repository = $site->github_repository;
        
        $this->database = $site->database;
        $this->favicon = $site->favicon;
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

    }

    /**
    * Close the modal and reset state
    */
   public function closeModal()
   {
       $this->show_modal = false;
       $this->reset();
       
   }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $siteRequest = new SiteRequest($this->site_id);
        try {
            $this->validate($siteRequest->rules());
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('errorOccurred');
            throw $e;
        }
        if (empty($this->site_id)) {
            $this->site_id = 0;
        }

        $site = $this->save();
        info($this->stripe_key.$this->stripe_secret);

        // Save to stripe table only if stripe_key and stripe_secret are not empty
        if (!empty($this->stripe_key) && !empty($this->stripe_secret)) {
            Stripe::updateOrCreate(
                ['site_id' => $this->site_id],
                ['key' => $this->stripe_key, 'secret' => $this->stripe_secret]
            );
        }


        if (isset($this->photo)) {
            $this->site_id = $site->id;
            $this->photo->store(config('constants.APP_LOGO_PATH') . 'logos-' . $site->id, 's3');
            $this->logo_image = config('constants.CLOUDFRONT_ASSET_URL') . config('constants.APP_LOGO_PATH') . 'logos-' . $site->id . '/' . $this->photo->hashName();
            $this->save();
        }

        $this->current_user = \Illuminate\Support\Facades\Auth::user();
        
        $site->updateTeam($this->team_id);

        return redirect()->route('site.edit.mysite')
            ->with('success', 'Site edit successful.');
    }

    private function save()
    {
        $site = Site::updateOrCreate(['id' => $this->site_id], [
            'site_name' => $this->site_name,
            'description' => $this->description,
            'host' => $this->host,
            'main_color' => $this->main_color,
            'logo_image' => $this->logo_image,
            'database' => $this->database,
            'favicon' => $this->favicon,
            'supports_registration' => $this->supports_registration,
            'subteams_enabled' => $this->subteams_enabled,
            'invitation_only' => $this->invitation_only,
            'app_specific_js' => $this->app_specific_js,
            'app_specific_css' => $this->app_specific_css,
            'image_folder' => $this->image_folder,
            'github_repository' => $this->github_repository,
        ]);
        
        // Handle livestream settings based on does_livestreaming checkbox
        if ($this->does_livestreaming) {
            // Create or update livestream settings
            \App\Models\LivestreamSettings::addOrUpdate($site);
        } else {
            // Remove livestream settings if they exist
            \App\Models\LivestreamSettings::remove($site->id);
        }
        return $site;
    }

    public function render()
    {
        return view('livewire.site.create-or-edit', [
            'site_id' => $this->site_id,
            'team_selection' => $this->team_selection
        ]);
    }
}
