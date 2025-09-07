<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SitePages;
use App\Models\Site;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Prasso\BedrockHtmlEditor\Services\HtmlProcessingService;

class BedrockHtmlEditor extends Component
{
    public $siteId;
    public $pageId;
    public $sitePage;
    public $site;
    public $htmlContent;
    public $prompt = '';
    public $isProcessing = false;
    public $modificationHistory = [];
    public $selectedModification = null;
    public $viewMode = 'edit'; // 'edit', 'preview', 'history'
    
    protected $htmlProcessingService;
    
    protected $rules = [
        'prompt' => 'required|string|min:3',
    ];
    
    public function boot(HtmlProcessingService $htmlProcessingService)
    {
        $this->htmlProcessingService = $htmlProcessingService;
    }
    
    public function mount($siteId, $pageId)
    {
        $this->siteId = $siteId;
        $this->pageId = $pageId;
        $this->loadPageData();
    }
    
    public function render()
    {
        return view('livewire.bedrock-html-editor', [
            'siteId' => $this->siteId,
            'viewMode' => $this->viewMode,
            'sitePage' => $this->sitePage,
            'htmlContent' => $this->htmlContent,
            'modificationHistory' => $this->modificationHistory
        ]);
    }
    
    public function loadPageData()
    {
        $this->sitePage = SitePages::findOrFail($this->pageId);
        $this->site = Site::findOrFail($this->siteId);
        $this->htmlContent = $this->getPageHtmlContent();
        $this->loadModificationHistory();
    }
    
    public function modifyHtml()
    {
        $this->validate();
        
        $this->isProcessing = true;
        
        try {
            // Check if HTML content is empty
            if (empty(trim($this->htmlContent))) {
                $this->dispatch('notify', message: 'Error: HTML content is empty', type: 'error');
                $this->isProcessing = false;
                return;
            }
            
            // Call the service with the correct parameters
            $result = $this->htmlProcessingService->modifyHtml(
                $this->htmlContent,
                $this->prompt
                // Removed the extra parameters that don't match the method signature
            );
            
            if ($result['success']) {
                $this->htmlContent = $result['modified_html'];
                $this->savePageHtmlContent($this->htmlContent);
                $this->prompt = '';
                $this->loadModificationHistory();
                $this->dispatch('notify', message: 'HTML modified successfully!');
            } else {
                $this->dispatch('notify', message: 'Error: ' . ($result['error'] ?? 'Unknown error'), type: 'error');
            }
        } catch (\Exception $e) {
            Log::error('Error modifying HTML: ' . $e->getMessage());
            $this->dispatch('notify', message: 'Error: ' . $e->getMessage(), type: 'error');
        }
        
        $this->isProcessing = false;
    }
    
    public function createHtml()
    {
        $this->validate();
        
        $this->isProcessing = true;
        
        try {
            // Call the service with the correct parameters
            $result = $this->htmlProcessingService->createHtml(
                $this->prompt
                // Removed the extra parameters that don't match the method signature
            );
            
            if ($result['success']) {
                $this->htmlContent = $result['html'];
                $this->savePageHtmlContent($this->htmlContent);
                $this->prompt = '';
                $this->loadModificationHistory();
                $this->dispatch('notify', message: 'HTML created successfully!');
            } else {
                $this->dispatch('notify', message: 'Error: ' . ($result['error'] ?? 'Unknown error'), type: 'error');
            }
        } catch (\Exception $e) {
            Log::error('Error creating HTML: ' . $e->getMessage());
            $this->dispatch('notify', message: 'Error: ' . $e->getMessage(), type: 'error');
        }
        
        $this->isProcessing = false;
    }
    
    public function loadModificationHistory()
    {
        // Initialize with an empty array since getModifications method doesn't exist yet
        $this->modificationHistory = [];
        
        // Log that this feature is not implemented yet
        Log::info('Modification history feature is not implemented yet');
    }
    
    public function applyModification($modificationId)
    {
        // This method is not implemented yet since the HtmlProcessingService doesn't have applyModification
        $this->dispatch('notify', message: 'Modification history feature is not implemented yet', type: 'info');
        
        // Log that this feature is not implemented yet
        Log::info('Apply modification feature is not implemented yet');
    }
    
    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }
    
    protected function getPageHtmlContent()
    {
        // If the page has type 2 (S3 content), get it from S3
        if ($this->sitePage->type == 2) {
            $fileName = $this->sitePage->section . '_' . $this->sitePage->id . '.html';
            $filePath = $this->site->site_name . '/pages/' . $fileName;

            if (Storage::disk('s3')->exists($filePath)) {
                return Storage::disk('s3')->get($filePath);
            }
        }

        // Otherwise, return the description field (which contains HTML for type 1)
        return $this->sitePage->description ?? '';
    }
    
    protected function savePageHtmlContent($htmlContent)
    {
        // If the page has type 2 (S3 content), save it to S3
        if ($this->sitePage->type == 2) {
            $fileName = $this->sitePage->section . '_' . $this->sitePage->id . '.html';
            $filePath = $this->site->site_name . '/pages/' . $fileName;

            Storage::disk('s3')->put($filePath, $htmlContent);
            Log::info('Saved HTML content to S3', ['path' => $filePath]);
        } else {
            // Otherwise, save it to the description field
            $this->sitePage->description = $htmlContent;
            $this->sitePage->save();
            Log::info('Saved HTML content to database', ['page_id' => $this->sitePage->id]);
        }

        return true;
    }
}
