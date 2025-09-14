<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SitePages;
use App\Models\Site;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Prasso\BedrockHtmlEditor\Services\HtmlProcessingService;

class AiSitePageCreator extends Component
{
    public $siteId;
    public $site;
    public $section = '';
    public $title = '';
    public $type = 1; // Default to HTML Content
    public $prompt = '';
    public $isProcessing = false;
    public $htmlContent = '';
    public $showPreview = false;
    
    protected $htmlProcessingService;
    
    protected $rules = [
        'section' => 'required|string|min:3',
        'title' => 'required|string|min:3',
        'prompt' => 'required|string|min:10',
    ];
    
    public function boot(HtmlProcessingService $htmlProcessingService)
    {
        $this->htmlProcessingService = $htmlProcessingService;
    }
    
    public function mount($siteId)
    {
        $this->siteId = $siteId;
        $this->loadSiteData();
    }
    
    public function render()
    {
        return view('livewire.ai-site-page-creator');
    }
    
    public function loadSiteData()
    {
        $this->site = Site::findOrFail($this->siteId);
    }
    
    public function generateContent()
    {
        $this->validate();
        
        $this->isProcessing = true;
        
        try {
            // Call the service to create HTML based on the prompt
            $result = $this->htmlProcessingService->createHtml($this->prompt);
            
            if ($result['success']) {
                $this->htmlContent = $result['html'];
                $this->showPreview = true;
                $this->dispatch('notify', message: 'HTML content generated successfully! Review and save if you like it.');
            } else {
                $this->dispatch('notify', message: 'Error: ' . ($result['error'] ?? 'Unknown error'), type: 'error');
            }
        } catch (\Exception $e) {
            Log::error('Error generating HTML content: ' . $e->getMessage());
            $this->dispatch('notify', message: 'Error: ' . $e->getMessage(), type: 'error');
        }
        
        $this->isProcessing = false;
    }
    
    public function savePage()
    {
        $this->validate([
            'section' => 'required|string|min:3',
            'title' => 'required|string|min:3',
            'htmlContent' => 'required',
        ]);
        
        try {
            // Create a new site page
            $sitePage = new SitePages();
            $sitePage->fk_site_id = $this->siteId;
            $sitePage->section = $this->section;
            $sitePage->title = $this->title;
            $sitePage->type = $this->type;
            
            if ($this->type == 1) {
                // HTML Content
                $sitePage->description = $this->htmlContent;
            } else if ($this->type == 2) {
                // S3 File
                $fileName = $this->section . '_' . time() . '.html';
                $filePath = $this->site->site_name . '/pages/' . $fileName;
                
                Storage::disk('s3')->put($filePath, $this->htmlContent);
                Log::info('Saved HTML content to S3', ['path' => $filePath]);
            }
            
            $sitePage->save();
            
            // Save to modification history
            $metadata = [
                'site_id' => $this->siteId,
                'page_id' => $sitePage->id,
            ];
            
            $this->htmlProcessingService->saveModificationHistory(
                $this->siteId,
                $sitePage->id,
                'Initial AI-Generated Content: ' . substr($this->prompt, 0, 50) . '...',
                $this->prompt,
                '',
                $this->htmlContent,
                null,
                $metadata
            );
            
            // Reset form
            $this->reset(['section', 'title', 'prompt', 'htmlContent', 'showPreview']);
            
            $this->dispatch('notify', message: 'Page created successfully!');
            $this->dispatch('pageCreated');
            
        } catch (\Exception $e) {
            Log::error('Error saving page: ' . $e->getMessage());
            $this->dispatch('notify', message: 'Error: ' . $e->getMessage(), type: 'error');
        }
    }
    
    public function cancelPreview()
    {
        $this->showPreview = false;
    }
}
