<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SitePages;
use App\Models\Site;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Prasso\BedrockHtmlEditor\Services\HtmlProcessingService;

class BedrockHtmlEditorController extends Controller
{
    protected $htmlProcessingService;

    public function __construct(HtmlProcessingService $htmlProcessingService)
    {
        $this->htmlProcessingService = $htmlProcessingService;
    }

    /**
     * Show the Bedrock HTML Editor for a specific site page
     */
    public function edit($siteId, $pageId)
    {
        // Get the site page
        $sitePage = SitePages::findOrFail($pageId);
        $site = Site::findOrFail($siteId);

        // Get the current HTML content
        $htmlContent = $this->getPageHtmlContent($sitePage, $site);

        // Instead of passing variables directly to the view, we'll render a parent view
        // that includes the Livewire component with the necessary parameters
        return view('bedrock-html-editor', [
            'siteId' => $siteId,
            'pageId' => $pageId
        ]);
    }

    /**
     * Process the HTML modification request
     */
    public function update(Request $request, $siteId, $pageId)
    {
        $request->validate([
            'prompt' => 'required|string',
            'html' => 'required|string',
        ]);

        $sitePage = SitePages::findOrFail($pageId);
        $site = Site::findOrFail($siteId);

        // Use the Bedrock HTML Editor service to modify the HTML
        $result = $this->htmlProcessingService->modifyHtml(
            $request->input('html'),
            $request->input('prompt')
        );

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['error']]);
        }

        // Save the modified HTML
        $this->savePageHtmlContent($sitePage, $site, $result['modified_html']);

        return back()->with('success', 'HTML content updated successfully');
    }

    /**
     * Process the HTML creation request
     */
    public function create(Request $request, $siteId, $pageId)
    {
        $request->validate([
            'prompt' => 'required|string',
        ]);

        $sitePage = SitePages::findOrFail($pageId);
        $site = Site::findOrFail($siteId);

        // Use the Bedrock HTML Editor service to create new HTML
        $result = $this->htmlProcessingService->createHtml($request->input('prompt'));

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['error']]);
        }

        // Save the created HTML
        $this->savePageHtmlContent($sitePage, $site, $result['html']);

        return back()->with('success', 'HTML content created successfully');
    }

    /**
     * Get the HTML content for a site page
     */
    protected function getPageHtmlContent($sitePage, $site)
    {
        // If the page has type 2 (S3 content), get it from S3
        if ($sitePage->type == 2) {
            $fileName = $sitePage->section . '_' . $sitePage->id . '.html';
            $filePath = $site->site_name . '/pages/' . $fileName;

            if (Storage::disk('s3')->exists($filePath)) {
                return Storage::disk('s3')->get($filePath);
            }
        }

        // Otherwise, return the description field (which contains HTML for type 1)
        return $sitePage->description ?? '';
    }

    /**
     * Save the HTML content for a site page
     */
    protected function savePageHtmlContent($sitePage, $site, $htmlContent)
    {
        // If the page has type 2 (S3 content), save it to S3
        if ($sitePage->type == 2) {
            $fileName = $sitePage->section . '_' . $sitePage->id . '.html';
            $filePath = $site->site_name . '/pages/' . $fileName;

            Storage::disk('s3')->put($filePath, $htmlContent);
            Log::info('Saved HTML content to S3', ['path' => $filePath]);
        } else {
            // Otherwise, save it to the description field
            $sitePage->description = $htmlContent;
            $sitePage->save();
            Log::info('Saved HTML content to database', ['page_id' => $sitePage->id]);
        }

        return true;
    }
}
