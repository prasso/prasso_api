<?php

namespace App\Livewire\Apps;

use Livewire\Component;
use app\Models\Apps;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Livewire\WithFileUploads;
use App\Models\Site;

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
    public $site_name;

    public $show_success;
    public $show_deployment_instructions = false;
    public $previous_pwa_server_url = null;

    public function mount()
    {
        // Initialize site_id from the existing app if editing
        if ($this->teamapp && $this->teamapp->site_id) {
            $this->site_id = $this->teamapp->site_id;
        }
        // Initialize site_name from site_id
        $this->site_name = Site::find($this->site_id)?->name ?? 'site';
        
        // Store the initial PWA server URL to detect changes
        $this->previous_pwa_server_url = $this->teamapp->pwa_server_url ?? null;
        
        // Auto-populate PWA Server URL if not already set
        if (!$this->teamapp->pwa_server_url) {
            $this->teamapp->pwa_server_url = $this->getNextAvailableServerUrl();
        }
    }
    
    /**
     * Get the next available port for PWA servers
     * Scans existing apps to find used ports and returns the next available one
     * Default starting port is 3001
     */
    private function getNextAvailableServerUrl()
    {
        $baseUrl = 'http://localhost:';
        $startPort = 3001;
        
        // Get all apps with pwa_server_url set
        $apps = Apps::whereNotNull('pwa_server_url')
            ->pluck('pwa_server_url')
            ->toArray();
        
        // Extract ports from URLs
        $usedPorts = [];
        foreach ($apps as $url) {
            // Parse URL to extract port
            $parsed = parse_url($url);
            if (isset($parsed['port'])) {
                $usedPorts[] = $parsed['port'];
            } elseif (isset($parsed['host'])) {
                // If no explicit port, try to extract from host:port format
                if (strpos($parsed['host'], ':') !== false) {
                    list($host, $port) = explode(':', $parsed['host']);
                    $usedPorts[] = (int)$port;
                }
            }
        }
        
        // Find next available port
        $nextPort = $startPort;
        while (in_array($nextPort, $usedPorts)) {
            $nextPort++;
        }
        
        return $baseUrl . $nextPort;
    }
    
    /**
     * Watch for changes to pwa_server_url and show deployment instructions
     */
    public function updated($property, $value)
    {
        if ($property === 'teamapp.pwa_server_url') {
            // Show modal if PWA Server URL is being set (not empty and different from before)
            if (!empty($value) && $value !== $this->previous_pwa_server_url) {
                $this->show_deployment_instructions = true;
            }
        }
    }
    
    /**
     * Close the deployment instructions modal
     */
    public function closeDeploymentInstructions()
    {
        $this->show_deployment_instructions = false;
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
        'teamapp.pwa_server_url' => 'nullable|url|max:2048',
        'teamapp.appicon' => 'required_without:photo',
        'teamapp.site_id' => 'required|min:1',
        'teamapp.sort_order' => 'required',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120'
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
        
        // Check if PWA Server URL was auto-populated and is being saved for the first time
        if ($this->teamapp->pwa_server_url && !$this->previous_pwa_server_url) {
            // This is a new PWA Server URL (was auto-populated), show instructions after save
            $this->show_deployment_instructions = true;
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
        
        // Update previous_pwa_server_url after save so modal doesn't show on next edit
        $this->previous_pwa_server_url = $this->teamapp->pwa_server_url;
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
