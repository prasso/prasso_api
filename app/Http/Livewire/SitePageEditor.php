<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\SitePages;
use App\Models\MasterPage;
use App\Models\SitePageTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Site;

class SitePageEditor extends Component
{
    public $sitePages,$site_name,$fk_site_id, $section, $title, $description, $url, $sitePage_id, $masterpage,$template, $login_required;
    
    public $isOpen = 0;
    public $isVisualEditorOpen = 0;
    public $siteid;

    public $site;
    
    public function mount( $siteid)
    {
        if ($siteid == '0')
        {
            $site = new Site();
            $site->site_name = 'New Site';
            $site->id = 0;
            $site->host = 'newsite';
            $site->main_color = '#000000';
            
        }
        else{
            $site = Site::where('id',$siteid)->first()->toArray();
        }
        $this->site = $site;
        $this->site_name = $site['site_name'];
        $this->siteid = $siteid;
    }

    public function render()
    {
        $masterpage_recs = MasterPage::orderBy('pagename')->get();
        $template_recs = SitePageTemplate::orderBy('templatename')->get();
        $this->sitePages = SitePages::where('fk_site_id', $this->siteid)->get();

        return view('livewire.site-page-editor')
            ->with('masterpage_recs', $masterpage_recs)
            ->with('template_recs', $template_recs);
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
        $this->masterpage = '';
        $this->login_required = false;
        
    }

    /**
     * 
     *
     * @var array
     */
    public function store()
    {
        $this->section = preg_replace('/\s+/', '', $this->section??'');
        $this->validate([
            'section' => "required|unique:site_pages,section,{{ $this->sitePage_id }},id,fk_site_id,{{ $this->siteid }}",
            'title' => 'required',
            'description' => 'required',
            'masterpage' => 'required',
            'login_required' => 'required',
        ]);
       SitePages::updateOrCreate(['id' => $this->sitePage_id], [
            'fk_site_id' => $this->siteid,
            'section' => $this->section,
            'title' => $this->title,
            'description' => $this->description,
            'url' => $this->url,
            'masterpage' => $this->masterpage,
            'login_required' => $this->login_required,
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
        $this->masterpage = $sitePage->masterpage;
        $this->login_required = $sitePage->login_required;

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
        $this->masterpage = $sitePage->masterpage;
        $this->login_required = $sitePage->login_required;

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
