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
    public $viewMode = 'edit'; // 'edit', 'preview', 'history', 'confirm'
    
    // Properties for the preview-and-confirm workflow
    public $originalHtml = null;
    public $modifiedHtml = null;
    public $showConfirmation = false;
    
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
            );
            
            if ($result['success']) {
                // Store the original and modified HTML for confirmation
                $this->originalHtml = $this->htmlContent;
                $this->modifiedHtml = $result['modified_html'];
                
                // Switch to confirmation view
                $this->showConfirmation = true;
                $this->viewMode = 'confirm';
                
                $this->dispatch('notify', message: 'HTML modification generated. Please review and confirm the changes.');
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
        try {
            $result = $this->htmlProcessingService->getModificationHistory(
                $this->siteId,
                $this->pageId,
                10, // Limit to 10 records
                0   // Start from the first record
            );
            
            if ($result['success']) {
                $this->modificationHistory = $result['modifications'];
            } else {
                Log::warning('Failed to load modification history', [
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
                $this->modificationHistory = [];
            }
        } catch (\Exception $e) {
            Log::error('Error loading modification history', [
                'error' => $e->getMessage()
            ]);
            $this->modificationHistory = [];
        }
    }
    
    public function applyModification($modificationId)
    {
        try {
            // Find the modification in the history
            $modification = \Prasso\BedrockHtmlEditor\Models\HtmlModification::find($modificationId);
            
            if (!$modification) {
                $this->dispatch('notify', message: 'Modification not found', type: 'error');
                return;
            }
            
            // Check if the modification belongs to this site and page
            if ($modification->site_id != $this->siteId || $modification->page_id != $this->pageId) {
                $this->dispatch('notify', message: 'Invalid modification for this page', type: 'error');
                return;
            }
            
            // Apply the modification by restoring the HTML content
            $this->htmlContent = $modification->modified_html;
            $this->savePageHtmlContent($this->htmlContent);
            
            // Save a new modification history entry for this restore action
            $this->htmlProcessingService->saveModificationHistory(
                $this->siteId,
                $this->pageId,
                'Restored from history: ' . $modification->title,
                'Restored from previous version created at ' . $modification->created_at,
                $this->getPageHtmlContent(), // Current content before restore
                $modification->modified_html, // Content being restored
                null,
                ['restored_from_id' => $modification->id]
            );
            
            // Reload the modification history
            $this->loadModificationHistory();
            
            $this->dispatch('notify', message: 'Successfully restored from history', type: 'success');
        } catch (\Exception $e) {
            Log::error('Error applying modification', [
                'error' => $e->getMessage(),
                'modification_id' => $modificationId
            ]);
            $this->dispatch('notify', message: 'Error: ' . $e->getMessage(), type: 'error');
        }
    }
    
    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
        
        // Reset confirmation state when switching away from confirm mode
        if ($mode !== 'confirm') {
            $this->showConfirmation = false;
        }
    }
    
    /**
     * Confirm and apply the AI-generated changes
     */
    public function confirmChanges()
    {
        try {
            // Apply the modified HTML
            $this->htmlContent = $this->modifiedHtml;
            
            // Save the changes
            $this->savePageHtmlContent($this->htmlContent);
            
            // Save to modification history
            $metadata = [
                'site_id' => $this->siteId,
                'page_id' => $this->pageId,
            ];
            
            $this->htmlProcessingService->saveModificationHistory(
                $this->siteId,
                $this->pageId,
                'HTML Modification: ' . substr($this->prompt, 0, 50) . '...',
                $this->prompt,
                $this->originalHtml,
                $this->modifiedHtml,
                null,
                $metadata
            );
            
            // Reset state
            $this->prompt = '';
            $this->originalHtml = null;
            $this->modifiedHtml = null;
            $this->showConfirmation = false;
            $this->viewMode = 'edit';
            
            // Reload modification history
            $this->loadModificationHistory();
            
            $this->dispatch('notify', message: 'Changes applied successfully!');
        } catch (\Exception $e) {
            Log::error('Error confirming changes: ' . $e->getMessage());
            $this->dispatch('notify', message: 'Error: ' . $e->getMessage(), type: 'error');
        }
    }
    
    /**
     * Reject the AI-generated changes
     */
    public function rejectChanges()
    {
        // Reset state without saving
        $this->originalHtml = null;
        $this->modifiedHtml = null;
        $this->showConfirmation = false;
        $this->viewMode = 'edit';
        
        $this->dispatch('notify', message: 'Changes rejected.');
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
