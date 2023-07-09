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
    public $sitePages,$site_name,$fk_site_id, $section, $title, $description, $url, $sitePage_id, $masterpage,$template,$style, $login_required,$user_level, $headers, $where_value;
    
    public $https_host;

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

            $site = $site->toArray();
            
        }
        else{
            $site = Site::where('id',$siteid)->first()->toArray();
        }
        $this->site = $site;
        $this->site_name = $site['site_name'];
        $this->siteid = $siteid;
        $this->https_host = $this->getHttpsHost($site['host']);
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

    private function getHttpsHost($host) {
        if (empty($host)) {
            return '';
        }

        if (strpos($host, ',') !== false) {
            $host_array = explode(',', $host);
            $host = $host_array[0];
        }
        if (strpos($host, 'https') !== false) {
            return $host;
        }
        $https_host = $host;
        if (strpos($host, 'http') !== false) {
            $https_host = str_replace('http', 'https', $host);
        }
        else{
            $https_host = 'https://' . $host;
        }
        return $https_host;
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
        $this->user_level = 0;
        $this->template = '' ;
        $this->style = '';
        $this->where_value = '';
        
    }

    /**
     * 
     *
     * @var array
     */
    public function store()
    {
        $this->section = preg_replace('/\s+/', '', $this->section??'');
        //if url starts with https and is longer than 5 characters then description is not required
        $this->validate([
            'section' => "required|unique:site_pages,section,{{ $this->sitePage_id }},id,fk_site_id,{{ $this->siteid }}",
            'title' => 'required',
            'description' => 'required_without:url',
            'masterpage' => 'required_without:url',
            'url' => 'required_if:masterpage,null',
            'login_required' => 'required',
            'user_level' => 'required',
            'where_value'=> 'nullable',
        ]);
        
        //specifying a template will launch code to run to gather data when the site-page is loaded
       SitePages::updateOrCreate(['id' => $this->sitePage_id], [
            'fk_site_id' => $this->siteid,
            'section' => $this->section,
            'title' => $this->title,
            'description' => $this->description,
            'url' => $this->url,
            'masterpage' => $this->masterpage,
            'login_required' => $this->login_required,
            'user_level' => $this->user_level,
            'headers' => $this->headers,
            'template' => $this->template,
            'style' => $this->style,
            'where_value' => $this->where_value,
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
        $this->user_level = $sitePage->user_level;
        $this->template = $sitePage->template;
        $this->style = $sitePage->style;
        $this->where_value = $sitePage->where_value;

        $this->openModal();
    }

    public function updated($propertyName)
    {
        if ($propertyName == 'masterpage' && $this->masterpage != '') {
            $this->url = '';
        }
        if ($propertyName == 'template' && $this->template != '' && $this->description == '') {
            $this->description = '<div x-data=\"[DATA]\"></div>';
        }
        $this->resetErrorBag($propertyName);
     
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
        $this->user_level = $sitePage->user_level;
        $this->template = $sitePage->template;
        $this->style = $sitePage->style;
        $this->where_value = $sitePage->where_value;

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
