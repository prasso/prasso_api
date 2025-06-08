<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SitePages;
use App\Models\MasterPage;
use App\Models\SitePageTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Site;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class SitePageEditor extends Component
{
    use WithFileUploads;
    public $sitePages,$site_name,$fk_site_id, $section, $title, $description, $url, $sitePage_id, $masterpage,$template,$style, $login_required,$user_level, $headers, $where_value, $page_notifications_on;
    
    public $type = 1; // Default to HTML content
    public $external_url;
    public $s3_file;
    public $s3_content;
    
    public $https_host;

    public $isOpen = 0;
    public $isVisualEditorOpen = 0;
    public $siteid;

    public $site;
    
    protected $rules = [
        's3_file' => 'nullable|file|mimes:html,htm|max:2048', // 2MB max
    ];

    public function mount($siteid)
    {
        //info('site page editor for siteid: '.$siteid);
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
        $site = Site::find($this->siteid);

        $team = $site->teams()->first();
        if ($team != null)
        {$team_selection = $team->pluck('name','id');}
        else
        {
            $team_selection=[];
        }
        return view('livewire.site-page-editor')
            ->with('masterpage_recs', $masterpage_recs)
            ->with('template_recs', $template_recs)
            ->with('team_selection', $team_selection);
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
        if (strpos($host, 'localhost:8000') !== false) {
            return 'http://localhost:8000';
        }
        $https_host = $host;
        if (strpos($host, 'http') !== false) {
            $https_host = str_replace('http', 'https', $host);
        } else {
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
        $this->page_notifications_on = false;
        
    }

    /**
     * 
     *
     * @var array
     */
    protected function logPageData($sitePage, $action = 'saved')
    {
        \Log::info("Site page {$action}:", [
            'id' => $sitePage->id,
            'site_id' => $sitePage->fk_site_id,
            'section' => $sitePage->section,
            'type' => $sitePage->type,
            'title' => $sitePage->title,
            'url' => $sitePage->url,
            'external_url' => $sitePage->external_url,
            'masterpage' => $sitePage->masterpage,
            'template' => $sitePage->template,
            'description_length' => strlen($sitePage->description ?? ''),
            'has_s3_content' => !empty($this->s3_content)
        ]);
    }

    public function store()
    {
        // Handle S3 file upload if needed
        if ($this->type == 2 && $this->s3_file) {
            $this->handleS3Upload();
        }

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
            'page_notifications_on' => 'required'
        ]);
        
        //specifying a template will launch code to run to gather data when the site-page is loaded
       $sitePage = SitePages::updateOrCreate(['id' => $this->sitePage_id], [
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
            'page_notifications_on' => $this->page_notifications_on,
            'type' => $this->type,
            'external_url' => $this->external_url,
        ]);
        
        // Log the saved data for debugging
        $this->logPageData($sitePage);
  
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
    public function updatedType($value)
    {
        if ($value == 2 && $this->sitePage_id) {
            // Load existing S3 content when switching to S3 type
            $this->loadS3Content();
        }
    }

    protected function loadS3Content()
    {
        try {
            // Get site name
            $site = Site::find($this->fk_site_id);
            if (!$site) {
                throw new \Exception('Site not found');
            }

            $fileName = $this->section . '_' . $this->sitePage_id . '.html';
            $filePath = $site->site_name . '/pages/' . $fileName;

            if (Storage::disk('s3')->exists($filePath)) {
                $this->s3_content = Storage::disk('s3')->get($filePath);
                \Log::info('Loaded S3 content:', ['path' => $filePath]);
            } else {
                $this->s3_content = null;
                \Log::info('No S3 content found:', ['path' => $filePath]);
            }
        } catch (\Exception $e) {
            \Log::error('Error loading S3 content: ' . $e->getMessage());
            $this->s3_content = null;
        }
    }

    protected function handleS3Upload()
    {
        \Log::info('Starting handleS3Upload');
        
        if (!$this->s3_file) {
            \Log::warning('No s3_file found in request');
            return;
        }

        \Log::info('File details before validation', [
            'original_name' => $this->s3_file->getClientOriginalName(),
            'mime_type' => $this->s3_file->getMimeType(),
            'size' => $this->s3_file->getSize(),
            'path' => $this->s3_file->getRealPath(),
            'extension' => $this->s3_file->getClientOriginalExtension()
        ]);

        $this->validate([
            's3_file' => 'required|file|mimes:html,htm|max:2048'
        ]);
        
        \Log::info('File passed validation');

        try {
            $file = $this->s3_file;
            
            // Get site name from site_id
            $site = Site::find($this->fk_site_id);
            if (!$site) {
                throw new \Exception('Site not found');
            }
            
            // If we're editing an existing page, use its ID
            if ($this->sitePage_id) {
                $fileName = $this->section . '_' . $this->sitePage_id . '.html';
            } else {
                // For new pages, we need to create the record first to get the ID
                $sitePage = SitePages::create([
                    'fk_site_id' => $this->fk_site_id,
                    'section' => $this->section,
                    'title' => $this->title,
                    'type' => 2 // S3 type
                ]);
                $fileName = $this->section . '_' . $sitePage->id . '.html';
                $this->sitePage_id = $sitePage->id;
            }
            
            $filePath = $site->site_name . '/pages/' . $fileName;
            
            // Read file contents before S3 upload
            \Log::info('Reading file contents', [
                'temp_path' => $file->getRealPath(),
                'exists' => file_exists($file->getRealPath())
            ]);
            
            // Get the file contents
            $contents = $file->get();
            if (empty($contents)) {
                throw new \Exception('Could not read file contents');
            }
            
            \Log::info('File contents read successfully', [
                'content_length' => strlen($contents)
            ]);
            
            // Store directly to S3
            \Log::info('Uploading to S3', [
                'path' => $filePath,
                'directory' => dirname($filePath),
                'filename' => basename($filePath)
            ]);
            
            $result = Storage::disk('s3')->put($filePath, $contents);
            
            if (!$result) {
                throw new \Exception('Failed to upload file to S3');
            }
            
            \Log::info('S3 upload successful');
            
            // Update the content preview with the contents we already have
            $this->s3_content = $contents;
            
            // Clear the file input
            $this->s3_file = null;
            
            session()->flash('message', 'File uploaded to S3 successfully.');
            
            Log::info('S3 file uploaded successfully', [
                'path' => $filePath,
                'site_id' => $this->fk_site_id,
                'section' => $this->section
            ]);
            
        } catch (\Exception $e) {
            Log::error('S3 upload failed: ' . $e->getMessage(), [
                'site_id' => $this->fk_site_id,
                'section' => $this->section
            ]);
            session()->flash('error', 'Failed to upload file to S3: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $sitePage = SitePages::findOrFail($id);
        $this->sitePage_id = $id;
        $this->fk_site_id = $sitePage->fk_site_id;
        $this->section = $sitePage->section;
        $this->title = $sitePage->title;
        $this->description = $sitePage->description;
        $this->url = $sitePage->url;
        $this->headers = $sitePage->headers;
        $this->masterpage = $sitePage->masterpage;
        $this->template = $sitePage->template;
        $this->style = $sitePage->style;
        $this->login_required = $sitePage->login_required;
        $this->user_level = $sitePage->user_level;
        $this->where_value = $sitePage->where_value;
        $this->page_notifications_on = $sitePage->page_notifications_on;
        $this->type = $sitePage->type ?? 1;
        $this->external_url = $sitePage->external_url;
        
        // Log page data on load
        \Log::info('Loading site page for edit:', [
            'id' => $id,
            'site_id' => $sitePage->fk_site_id,
            'section' => $sitePage->section,
            'type' => $sitePage->type,
            'title' => $sitePage->title,
            'url' => $sitePage->url,
            'external_url' => $sitePage->external_url,
            'masterpage' => $sitePage->masterpage,
            'template' => $sitePage->template
        ]);
        
        if ($this->type == 2) {
            $this->loadS3Content();
        }
        
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
        $this->page_notifications_on = $sitePage->page_notifications_on;

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
