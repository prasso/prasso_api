<?php

namespace App\Livewire\Apps;

use Livewire\Component;
use app\Models\Apps;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Livewire\WithFileUploads;

class AppInfoForm extends Component
{

    use WithFileUploads;

    public $teamapp;
    public $teamapps;
    public $team;
    public $team_selection;
    public $team_id;
    /**
     * The site ID associated with the app.
     * Used to link apps to their respective sites in the system.
     */
    public $site_id;
    public $sites;
    public $photo;

    public $show_success;

    public function mount()
    {
        // Initialize site_id from the existing app if editing
        if ($this->teamapp && $this->teamapp->site_id) {
            $this->site_id = $this->teamapp->site_id;
        }
    }

    public function render()
    {
        return view('livewire.apps.app-info-form');
    }

    protected $rules = [
        'teamapp.app_name' => 'required|min:6',
        'teamapp.page_title' => 'required|min:6',
        'teamapp.page_url' => 'required|min:6',
        'teamapp.pwa_app_url' => 'nullable|url|max:2048',
        'teamapp.appicon' => 'required_without:photo',
        'teamapp.site_id' => 'required|min:1',
        'teamapp.sort_order' => 'required',
        'photo' => 'required_without:teamapp.appicon|image|mimes:jpeg,png,jpg,gif,svg|max:5120'
    ];

    protected $messages = [
        'photo.required_without' => 'Please upload an app icon or select an existing one.',
        'photo.image' => 'The file must be a valid image.',
        'photo.mimes' => 'The image must be a JPEG, PNG, JPG, GIF, or SVG file.',
        'photo.max' => 'The image size must not exceed 5MB.',
    ];

    public function updateApp()
    {
        $this->teamapp->team_id = $this->team_id;
        $this->teamapp->site_id = $this->site_id;
        $this->validate();

        // Execution doesn't reach here if validation fails.
        if (isset($this->photo))
        {
            // Store in the "photos" directory in a configured "s3" bucket.
            //prassouploads/prasso/-app-photos/logos-1/
            $storedPath = $this->photo->store(config('constants.APP_LOGO_PATH') .'logos-'.$this->teamapp->team_id, 's3');
            $this->teamapp->appicon = config('constants.CLOUDFRONT_ASSET_URL') . $storedPath;
        }
        
        // Check if PWA URL is being set and is a faxt.com domain
        if ($this->teamapp->pwa_app_url && $this->isFaxtDomain($this->teamapp->pwa_app_url)) {
            $pwaDomain = $this->extractDomain($this->teamapp->pwa_app_url);
            try {
                $exitCode = Artisan::call("dns:setup", [
                    'site' => $pwaDomain
                ]);
                if ($exitCode === 0) {
                    Log::info("DNS setup completed for PWA domain: {$pwaDomain}");
                } else {
                    Log::warning("DNS setup returned exit code {$exitCode} for PWA domain: {$pwaDomain}");
                }
            } catch (\Throwable $e) {
                Log::error("DNS setup exception for PWA domain {$pwaDomain}: " . $e->getMessage());
            }
        }
        
        Apps::processUpdates($this->teamapp->toArray()  );
        $this->show_success = true;
    }

    /**
     * Check if a URL is based on the faxt.com domain
     */
    private function isFaxtDomain($url)
    {
        return strpos($url, 'faxt.com') !== false;
    }

    /**
     * Extract domain from a URL (e.g., https://app.faxt.com -> app.faxt.com)
     */
    private function extractDomain($url)
    {
        $parsed = parse_url($url);
        return $parsed['host'] ?? $url;
    }
}
