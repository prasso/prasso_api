<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Site;

class SiteEditor extends Component
{
    public $sites, $site_id, $host,$main_color,$logo_image, $database, $favicon;
    public $current_user;
    public $isOpen = 0;
    
    public function mount(User $user, Request $request)
    {
        //does this user have an admin role?
Log::info('site editor user:'.json_encode($user));
       $this->current_user = $user;
    }

    public function render()
    {
        $this->sites = Site::all();
Log::info($this->sites);
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
        $this->host = '';
        $this->main_color = '';
        $this->logo_image = '';
        $this->database = '';
        $this->favicon = '';
        $this->site_id = '';
    }
     
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function store()
    {

   Log::info('validating');
        $this->validate([
            'host' => 'required',
            'main_color' => 'required',
            'logo_image' => 'required',
            'database' => 'required',
            'favicon' => 'required'
        ]);
   Log::info('saving');
        Site::updateOrCreate(['id' => $this->site_id], [
            'host' => $this->host,
            'main_color' => $this->main_color,
            'logo_image' => $this->logo_image,
            'database' => $this->database,
            'favicon' => $this->favicon,
        ]);
  
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
        $this->host = $site->host;
        $this->main_color = $site->main_color;
        $this->logo_image = $site->logo_image;
        $this->database = $site->database;
        $this->favicon = $site->favicon;

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
