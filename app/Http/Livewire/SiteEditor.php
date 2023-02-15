<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\TeamSite;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\SiteRequest;
use App\Models\Site;
use Auth;

class SiteEditor extends Component
{
    public $sites, $site_id,$site_name, $host,$main_color,$logo_image, $database, $favicon, $supports_registration, $app_specific_js, $app_specific_css;
    public $current_user;
    public $isOpen = 0;

    
    public function mount(User $user)
    {
        $this->current_user = $user;
    }

    public function render()
    {
        $this->sites = Site::all();
        return view('livewire.site-editor');
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
  
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    private function resetInputFields(){
        $this->site_name = '';
        $this->host = '';
        $this->main_color = '';
        $this->logo_image = '';
        $this->database = '';
        $this->favicon = '';
        $this->site_id = '';
        $this->supports_registration = false;
        $this->app_specific_js ='';
        $this->app_specific_css = '';
    }
     
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function store()
    {
        $siteRequest = new SiteRequest();
        $this->validate($siteRequest->rules());

        
        $newsite=false;
        if (empty($this->site_id))
        {
            $this->site_id = 0;
            $newsite=true;
        }
        $site = Site::updateOrCreate(['id' => $this->site_id], [
            'site_name' => $this->site_name,
            'host' => $this->host,
            'main_color' => $this->main_color,
            'logo_image' => $this->logo_image,
            'database' => $this->database,
            'favicon' => $this->favicon,
            'supports_registration' => $this->supports_registration,
            'app_specific_js' => $this->app_specific_js,
            'app_specific_css' => $this->app_specific_css,
        ]);
  
        // new sites need new team
        if ($newsite)
        {
            if ($this->current_user == null)
            {
                $this->current_user = Auth::user();
            }
            $site->createTeam($this->current_user->id);
        }
        session()->flash('message', 
            $this->site_id ? 'Site Updated Successfully.' : 'Site Created Successfully.');
  
        $this->closeModal();
        
        $this->resetInputFields();
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
        $this->host = $site->host;
        $this->main_color = $site->main_color;
        $this->logo_image = $site->logo_image;
        $this->database = $site->database;
        $this->favicon = $site->favicon;
        $this->supports_registration = $site->supports_registration;
        $this->app_specific_js = $site->app_specific_js;
        $this->app_specific_css = $site->app_specific_css;

        $this->openModal();
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
