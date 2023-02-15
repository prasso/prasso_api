<?php

namespace App\Http\Livewire\Site;

use Livewire\Component;
use App\Models\Site;
use App\Models\User;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\SiteRequest;
use Livewire\WithFileUploads;

class CreateOrEdit extends Component
{
    use WithFileUploads;

    public $siteid;
    public $site_name; //
    public $description; //
    public $host; //
    public $main_color; //
    public $logo_image; //
    public $supports_registration;//
    public $database;
    public $favicon;
    public $app_specific_js;
    public $app_specific_css;
    public $current_user;
    public $team;
    public $show_modal = true;

    public $photo;

        
    public function mount(Site $site, User $user, Team $team, $show_modal)
    {
        if ($site == null) $site = new Site();
        $this->show_modal = $show_modal;

        //does this user have an admin role?
        $this->current_user = $user;
        $this->team = $team;
        $this->siteid = $site->id;
        $this->site_name = $site->site_name;
        $this->description = $site->description;
        $this->host = $site->host;
        $this->main_color = $site->main_color;
        $this->logo_image = $site->logo_image;
        $this->supports_registration = $site->supports_registration;
        $this->database = $site->database;
        $this->favicon = $site->favicon;
        $this->app_specific_js = $site->app_specific_js;
        $this->app_specific_css = $site->app_specific_css;

    }

     /**
     * @var array
     */
    public function closeModal()
    {
        // a placeholder cause the modal is not used
        return redirect()->route('dashboard');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $siteRequest = new SiteRequest();
        $this->validate($siteRequest->rules());
log::info("CreateOrEdit store site 1");
        if (empty($this->id))
        {
            $this->id = 0;
        }
        
        $site = $this->save();

log::info("CreateOrEdit store site 2");
        if (isset($this->photo))
        {
            $this->siteid = $site->id;
            $this->photo->store(config('constants.APP_LOGO_PATH') .'logos-'.$site->id, 's3');
            $this->logo_image = config('constants.CLOUDFRONT_ASSET_URL') . config('constants.APP_LOGO_PATH') .'logos-'.$site->id.'/'. $this->photo->hashName();
            $this->save();
        }

log::info("CreateOrEdit store site 3");
        return redirect()->route('site.edit.mysite')
            ->with('success', 'Site edit successful.');
    }

    private function save(){
        $site = Site::updateOrCreate(['id' => $this->siteid], [
            'site_name' => $this->site_name,
            'description' => $this->description,
            'host' => $this->host,
            'main_color' => $this->main_color,
            'logo_image' => $this->logo_image,
            'database' => $this->database,
            'favicon' => $this->favicon,
            'supports_registration' => $this->supports_registration,
            'app_specific_js' => $this->app_specific_js,
            'app_specific_css' => $this->app_specific_css,
        ]);
        return $site;
    }

    public function render()
    {
        return view('livewire.site.create-or-edit');
    }
}
