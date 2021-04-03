<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\SitePages;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Site;

class SitePageEditor extends Component
{
    public $sitePages,$fk_site_id, $section, $title, $description, $url, $sitePage_id;
    
    public $isOpen = 0;
    public $isVisualEditorOpen = 0;
    public $site;
    
    public function mount( Request $request)
    {
        $host = $request->getHost();
        $site = Site::getClient($host);
        $this->site = $site;

    }

    public function render()
    {
        $this->sitePages = SitePages::all();

        return view('livewire.site-page-editor');
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
    public function openVisualModal()
    {
        $this->isVisualEditorOpen = true;
        
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
        $this->isVisualEditorOpen = false;
    }
  
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    private function resetInputFields(){
        $this->section = '';
        $this->title = '';
        $this->description = '';
        $this->url = '';
        $this->sitePage_id = '';
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
            'section' => 'required',
            'title' => 'required',
            'description' => 'required',
            'url' => 'required',
        ]);
   Log::info('saving');
        SitePages::updateOrCreate(['id' => $this->sitePage_id], [
            'fk_site_id' => $this->site->id,
            'section' => $this->section,
            'title' => $this->title,
            'description' => $this->description,
            'url' => $this->url,
        ]);
  
        session()->flash('message', 
            $this->sitePage_id ? 'Site Page Updated Successfully.' : 'Site Page Created Successfully.');
  
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
        $sitePage = SitePages::findOrFail($id);
        $this->sitePage_id = $id;
        $this->fk_site_id = $sitePage->fk_site_id;
        $this->section = $sitePage->section;
        $this->title = $sitePage->title;
        $this->description = $sitePage->description;
        $this->url = $sitePage->url;

        $this->openModal();
    }
    
       /**
     * can we make this work with GrapesJs? (it works when loaded from a new page, not from the livewire component)
     *
     * @var array
     */
    public function visualEditor($id)
    {
        $sitePage = SitePages::findOrFail($id);
        $this->sitePage_id = $id;
        $this->fk_site_id = $sitePage->fk_site_id;
        $this->section = $sitePage->section;
        $this->title = $sitePage->title;
        $this->description = $sitePage->description;
        $this->url = $sitePage->url;

        $this->openVisualModal();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function delete($id)
    {
        SitePages::find($id)->delete();
        session()->flash('message', 'Site Page Deleted Successfully.');
    }
}
