<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Providers\AppServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Services\SitePageService;
use App\Services\UserService;
use App\Models\SitePages;
use App\Models\PageView;
use App\Models\MasterPage;
use App\Models\Site;
use App\Models\SitePageData;
use App\Models\SitePageTemplate;
use Illuminate\Support\Str;
use App\Models\User;
use Livewire\Livewire;
use App\Http\Livewire\HeaderCarousel;
use Livewire\Mechanisms\ComponentRegistry;

class SitePageController extends BaseController
{
    protected $sitePageService;
    protected $userService;

    public function __construct(
        Request $request,
        SitePageService $sitePageService,
        UserService $userServ
    ) {
        parent::__construct($request);
        $this->sitePageService = $sitePageService;
        $this->userService = $userServ;
    }

    /**
     * return welcome page if one is defined for this site
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $welcomepage = null;
        $request = Request::capture();

        $user = Auth::user() ?? null;
        if ($user != null) {
            return $this->getDashboardForCurrentSite($user);
        }

        /**
         * PWA App Reverse Proxy Feature
         * 
         * For sites with a PWA app configured, proxy the request to the Node.js server
         * instead of the traditional Prasso welcome page. This allows sites to host
         * Progressive Web Apps without Apache vhost configuration.
         * 
         * Prerequisites:
         * - Site must have an associated app with pwa_app_url and pwa_server_url set
         * - The Node.js server must be running at the pwa_server_url location
         * 
         * Flow:
         * 1. Get the app associated with this site
         * 2. Check if the app has pwa_app_url and pwa_server_url configured
         * 3. If yes, proxy the request to the Node.js server
         * 4. Return the proxied response; otherwise, continue to traditional page handling
         */
        if ($this->site != null) {
            $app = $this->site->app;
            if ($app && !empty($app->pwa_app_url) && !empty($app->pwa_server_url)) {
                try {
                    $proxyResponse = $this->proxyRequestToServer($app->pwa_server_url, $request->path(), $request);
                    Log::info("Proxying PWA request to {$app->pwa_server_url} for app {$app->id} on site {$this->site->id}");
                    return $proxyResponse;
                } catch (\Exception $e) {
                    Log::error("Failed to proxy PWA request for app {$app->id}: {$e->getMessage()}");
                    // Fall through to traditional handling
                }
            }
        }

        /**
         * GitHub Repository Deployment Feature
         * 
         * For sites with a GitHub repository configured, serve the deployed repository's index.html
         * instead of the traditional Prasso welcome page. This allows sites to host static content
         * (e.g., single-page applications, documentation) directly from a GitHub repository.
         * 
         * Prerequisites:
         * - $site->github_repository must be set (format: "username/repository" or "org/repository")
         * - $site->deployment_path must be set (indicates the site is configured for GitHub hosting)
         * - The repository must be cloned/deployed to public/hosted_sites/{repository_name}/
         * 
         * Flow:
         * 1. Extract repository name from the github_repository path (e.g., "myrepo" from "user/myrepo")
         * 2. Construct path to index.html in the deployed repository directory
         * 3. If index.html exists, serve it directly; otherwise, continue to traditional page handling
         */
        if ($this->site != null && !empty($this->site->deployment_path) && !empty($this->site->github_repository)) {
            $repoName = explode('/', $this->site->github_repository)[1] ?? $this->site->github_repository;
            $indexPath = public_path($this->site->deployment_path . '/index.html');

            if (file_exists($indexPath)) {
                Log::info("Serving GitHub repository index page for site {$this->site->id}");
                return response()->file($indexPath);
            }
        }

        if ($this->site != null && strcmp($this->site->site_name, config('app.name')) != 0) {
            $welcomepage = SitePages::where('fk_site_id', $this->site->id)->where('section', 'Welcome')->first();
        }
        if ($welcomepage == null) {
            return view('welcome');
        }
        $welcomepage->description = $this->prepareTemplate($welcomepage, $request->path());
        $masterpage = $this->getMaster($welcomepage);

        return view($welcomepage->masterpage)
            ->with('sitePage', $welcomepage)
            ->with('site', $this->site)
            ->with('page_short_url', '/')
            ->with('masterPage', $masterpage);
    }
    public function loadLiveWireComponent($component, $pageid)
    {
        return view('sitepage.site-page-component')->with('component', $component)->with('pageid', $pageid);
    }
    /**
     * this code verifies that the user is a member of the current site's team
     * loads up the dashboard if the user is logged in and belongs to this site's team
     */
    private function getDashboardForCurrentSite($user)
    {
        // Check if user is a super admin on site ID 1 (Prasso main site)
        $isPrassoSuperAdmin = $user->isSuperAdmin() && $this->site->id == 1;

        // Only redirect instructors to the Filament admin panel if they are team owners for this specific site
        // or if they are super admins on any site
        if ($user->hasRole(config('constants.INSTRUCTOR'))) {
            // If this is the Prasso site (ID 1), only super admins should be redirected to admin
            if ($this->site->id == 1) {
                if ($isPrassoSuperAdmin) {
                    //do nothing code below will execute
                }
            } else {
                // For other sites, check if the user is a site admin for this specific site
               if ($user->isSuperAdmin()) {
                    // do nothing code below will execute
                } else {
                    if ( $user->isInstructor($this->site)) {
                        return redirect()->route('filament.site-admin.pages.dashboard');
                    }
                }
            }
        }

        $user_content = $this->getPage('Dashboard', $user);
        // Render the dashboard view with either custom content or default content
        return view('dashboard')->with('user_content', $user_content)
            ->with('site', $this->site);
    }

    private function getPage($page, $user)
    {

        $user->setCurrentToOwnedTeam();
        $request = Request::capture();

        if (!$this->userService->isUserOnTeam($user)) {
            Auth::logout();
            session()->flash('status', config('constants.LOGIN_AGAIN'));
            return redirect('/login');
        }
        $user_content = '';

        // if the site supports registration, check to see if the site has a $page site_page
        // Check if the current site exists and is not the main application site
        if ($this->site !== null && strcmp($this->site->site_name, config('app.name')) !== 0) {
            // Try to find a custom page for the current site
            $pageFound = SitePages::where('fk_site_id', $this->site->id)
                ->where('section', $page)
                ->first();

            // If a custom page exists, handle based on its type
            if ($pageFound !== null) {
                switch ($pageFound->type) {
                    case 2: // S3 File
                        $s3Content = $this->getS3PageContent($this->site->id, $page);
                        //log the s3 content
                        \Illuminate\Support\Facades\Log::info("S3 content: " . $s3Content);
                        if (!empty($s3Content)) {
                            $pageFound->description = $s3Content;
                        } else {
                            // If S3 content is not found, fall back to HTML content
                            $pageFound->type = 1;
                            \Illuminate\Support\Facades\Log::warning("S3 content not found, falling back to HTML for page: " . $page);
                        }
                        break;

                    case 3: // External URL
                        if (!empty($pageFound->external_url)) {
                            // Redirect to the external URL
                            return redirect()->away($pageFound->external_url);
                        }
                        // If no URL is provided, fall through to HTML
                        $pageFound->type = 1;
                        \Illuminate\Support\Facades\Log::warning("External URL not provided for page: " . $page);
                        break;

                        // Default case (type 1: HTML) falls through
                }

                $user_content = $this->prepareTemplate($pageFound, $request->path());
            } else {
                // For backward compatibility, check S3 for content
                $s3Content = $this->getS3PageContent($this->site->id, $page);

                if (!empty($s3Content)) {
                    // Create a temporary SitePages object to use with prepareTemplate
                    $tempPage = new SitePages();
                    $tempPage->fk_site_id = $this->site->id;
                    $tempPage->section = $page;
                    $tempPage->title = $page;
                    $tempPage->description = $s3Content;
                    $tempPage->type = 2; // Mark as S3 type

                    $user_content = $this->prepareTemplate($tempPage, $request->path());
                }
            }
        }
        // return the prepared content
        return $user_content;
    }

    /**
     * Get page content from S3 bucket
     * 
     * @param int $siteId The site ID
     * @param string $pageName The page name/section
     * @return string The page content or empty string if not found
     */
    /**
     * Get page content from S3 storage
     *
     * @param int $siteId The site ID
     * @param string $pageName The page name/section
     * @return string The page content or empty string if not found
     */
    protected function getS3PageContent($site_id, $page)
    {
        $site = Site::find($site_id);
        if (!$site) {
            \Log::error('Site not found for ID: ' . $site_id);
            return null;
        }

        // Get the page record to get its ID
        $sitePage = SitePages::where('fk_site_id', $site_id)
            ->where('section', $page)
            ->first();

        if (!$sitePage) {
            \Log::error('Page not found: ' . $page);
            return null;
        }

        $s3Path = $site->site_name . '/pages/' . $page . '_' . $sitePage->id . '.html';

        try {
            // Check if the file exists in S3
            if (!\Illuminate\Support\Facades\Storage::disk('s3')->exists($s3Path)) {
                \Illuminate\Support\Facades\Log::warning("S3 content not found: " . $s3Path);
                return '';
            }

            // Get the file content from S3
            return \Illuminate\Support\Facades\Storage::disk('s3')->get($s3Path);
        } catch (\Exception $e) {
            // Log the error but don't crash the application
            \Illuminate\Support\Facades\Log::error('Error fetching S3 page content: ' . $e->getMessage() .
                ' [Site: ' . $siteId . ', Page: ' . $pageName . ']');
            return '';
        }
    }


    private function getMaster($sitepage)
    {
        $masterPage = null;

        if (isset($sitepage->masterpage)) {
            // If the sitepage has a masterpage specified, use it
            $masterPage = MasterPage::where('pagename', $sitepage->masterpage)->first();
        } elseif (isset($this->masterpage)) {
            // If the sitepage doesn't have a masterpage, use controller's masterpage if available
            $masterPage = MasterPage::where('pagename', $this->masterpage)->first();
        }

        return $masterPage;
    }

    private function prepareTemplate($pageToProcess, $path = null)
    {

        $page_content = $pageToProcess->description;
        $user = Auth::user() ?? null;
        $team = $this->site->teamFromSite();
        //First,Check if the header placeholder exists, 
        //then replace it with the header page if defined
        if (strpos($page_content, '[HEADER]') !== false) {
            //the header text that will be placed into $page_content
            // is an actual page for this site. a page that has the title of [HEADER]
            $headerPage = SitePages::where('fk_site_id', $this->site->id)
                ->where('title', '[HEADER]')->first();
            if ($headerPage !== null) {
                $page_content = str_replace('[HEADER]', $headerPage->description, $page_content);
            }
        }
        //replace the tokens in the dashboard page with the user's name, email, and profile photo
        $page_content = str_replace('@csrf', '<input type="hidden" name="_token" value="' . csrf_token() . '">', $page_content);
        $page_content = str_replace('CSRF_TOKEN', csrf_token(), $page_content);
        $page_content = str_replace('@csrf_token()', csrf_token(), $page_content);
        $page_content = str_replace('@csrf', '<input type="hidden" value="' . csrf_token() . '" />', $page_content);
        $page_content = str_replace('[TEAM_ID]', $team->id, $page_content);
        $page_content = str_replace('{{ $team_id }}', $team->id, $page_content);
        $page_content = str_replace('MAIN_SITE_COLOR', $this->site->main_color, $page_content);
        $page_content = str_replace('[SITE_CSS]', $this->site->app_specific_css, $page_content);
        $page_content = str_replace('SITE_MAP', $this->site->getSiteMapList($path), $page_content);
        $page_content = str_replace('SITE_NAME', $this->site->site_name, $page_content);
        $page_content = str_replace('SITE_LOGO_FILE', $this->site->logo_image, $page_content);
        $page_content = str_replace('SITE_FAVICON_FILE', $this->site->favicon, $page_content);
        $page_content = str_replace('SITE_DESCRIPTION', $this->site->description, $page_content);
        $page_content = str_replace('PAGE_NAME', $pageToProcess->title, $page_content);
        $page_content = str_replace('PAGE_SLUG', $pageToProcess->section, $page_content);
        $page_content = str_replace('[SITE_ID]', $this->site->id, $page_content);
        $page_content = str_replace('[DATA_PAGE_ID]', $pageToProcess->id, $page_content);


        //Check if the carousel placeholder exists, then replace it with the Livewire component
        if (strpos($page_content, '[CAROUSEL_COMPONENT]') !== false) {

            $page_content = str_replace(
                '[CAROUSEL_COMPONENT]',
                '<div id="carouseldiv"></div>
                <script>
                document.addEventListener("DOMContentLoaded", function() {
                    loadLivewireComponent("prasso-flipper", "carouseldiv", ' . $pageToProcess->id . ');
                });</script>',
                $page_content
            );
        }

        if ($user != null) {
            $page_content = str_replace('USER_NAME', $user->name, $page_content);
            $page_content = str_replace('USER_EMAIL', $user->email, $page_content);
            $page_content = str_replace('USER_PROFILE_PHOTO', $user->getProfilePhoto(), $page_content);
        }

        return $page_content;
    }

    /**
     * return welcome page
     *
     * @return \Illuminate\Http\Response
     */
    public function viewSitePage(Request $request, $section)
    {
        if ($section == 'favicon.ico') {
            return null;
        }

        /**
         * PWA App Reverse Proxy Page Serving
         * 
         * For sites with a PWA app configured, proxy the request to the Node.js server
         * instead of serving static files. This enables hosting of Progressive Web Apps
         * with full server-side functionality.
         * 
         * Prerequisites:
         * - Site must have an associated app with pwa_app_url and pwa_server_url set
         * - The Node.js server must be running at the pwa_server_url location
         * 
         * Flow:
         * 1. Proxy the request to the Node.js server at pwa_server_url
         * 2. Return the proxied response
         * 3. If proxy fails, fall through to Prasso's page system
         */
        if ($this->site != null) {
            $app = $this->site->app;
            if ($app && !empty($app->pwa_app_url) && !empty($app->pwa_server_url)) {
                try {
                    $proxyResponse = $this->proxyRequestToServer($app->pwa_server_url, '/' . $section, $request);
                    Log::info("Proxying PWA page request for {$section} to {$app->pwa_server_url} for app {$app->id} on site {$this->site->id}");
                    return $proxyResponse;
                } catch (\Exception $e) {
                    Log::error("Failed to proxy PWA page request for {$section} on app {$app->id}: {$e->getMessage()}");
                    // Fall through to Prasso's page system
                }
            }
        }

        /**
         * GitHub Repository Page Serving
         * 
         * For sites with a GitHub repository configured, attempt to serve requested pages
         * directly from the deployed repository before falling back to Prasso's page system.
         * This enables hosting of static content and single-page applications.
         * 
         * Prerequisites:
         * - $site->github_repository must be set (format: "username/repository" or "org/repository")
         * - $site->deployment_path must be set (indicates the site is configured for GitHub hosting)
         * - The repository must be cloned/deployed to public/hosted_sites/{repository_name}/
         * 
         * Resolution order:
         * 1. Try to serve the exact file path (e.g., /page/about -> hosted_sites/repo/page/about)
         * 2. Try to serve with .html extension (e.g., /page/about -> hosted_sites/repo/page/about.html)
         * 3. Try to serve index.html from a directory (e.g., /page/about -> hosted_sites/repo/page/about/index.html)
         * 4. If none exist, fall through to Prasso's page system
         */
        if ($this->site != null && !empty($this->site->deployment_path) && !empty($this->site->github_repository)) {
            $repoName = explode('/', $this->site->github_repository)[1] ?? $this->site->github_repository;
            $pagePath = public_path('hosted_sites/' . $repoName . '/' . $section);

            // Check if the requested page exists in the repository
            if (file_exists($pagePath)) {
                Log::info("Serving GitHub repository page {$section} for site {$this->site->id}");
                return response()->file($pagePath);
            } else if (file_exists($pagePath . '.html')) {
                Log::info("Serving GitHub repository page {$section}.html for site {$this->site->id}");
                return response()->file($pagePath . '.html');
            } else if (is_dir($pagePath) && file_exists($pagePath . '/index.html')) {
                Log::info("Serving GitHub repository directory index for {$section} for site {$this->site->id}");
                return response()->file($pagePath . '/index.html');
            }
        }

        $user = Auth::user() ?? null;
        if ($user == null) {
            $user = $this->setUpUser($request, $user);
        }
        $sitepage = SitePages::where('fk_site_id', $this->site->id)->where('section', $section)->first();

        if ($sitepage == null) {
            //check for privacy and for terms of service, if shown then provide the Prasso version
            $matches = array_filter(AppServiceProvider::$allowedUris, function ($allowedUri) use ($section) {
                return strpos($section, $allowedUri) !== false;
            });
            if (count($matches) > 0) {
                AppServiceProvider::loadDefaultsForPagesNotUsingControllerClass($this->site);
                return view($section);
            }

            /**
             * GitHub Repository Fallback
             * 
             * If a specific page is not found in Prasso's page system and the site has a GitHub
             * repository configured, serve the repository's index.html as a fallback. This is useful
             * for single-page applications (SPAs) that handle routing client-side.
             * 
             * This fallback only applies if:
             * - The page was not found in Prasso's SitePages table
             * - The site has both deployment_path and github_repository configured
             * - The repository's index.html exists
             */
            if ($this->site != null && !empty($this->site->deployment_path) && !empty($this->site->github_repository)) {
                $repoName = explode('/', $this->site->github_repository)[1] ?? $this->site->github_repository;
                $indexPath = public_path('hosted_sites/' . $repoName . '/index.html');

                if (file_exists($indexPath)) {
                    Log::info("Page {$section} not found, serving GitHub repository index page as fallback for site {$this->site->id}");
                    return response()->file($indexPath);
                }
            }

            info('viewSitePage page not found, using system welcome: ' . $section . ' site:' . $this->site->id);
            return view('welcome');
        }

        if (($sitepage->requiresAuthentication() && $user == null) ||
            ($user != null && !$this->userService->isUserOnTeam($user))
        ) {
            if ($user == null) {
                \App\Http\Middleware\UserPageAccess::authorizeUser($request);
                $user = Auth::user() ?? null;
                if ($user == null) {
                    Auth::logout();
                    session()->flash('status', config('constants.LOGIN_AGAIN'));
                    return redirect('/login');
                }
            }
            if (($user != null && !$this->userService->isUserOnTeam($user))) {
                Auth::logout();
                session()->flash('status', config('constants.LOGIN_AGAIN'));
                return redirect('/login');
            }
        }
        //if the page requires admin, and the user isn't an admin, inform them no access to this page
        if ($sitepage->pageRequiresAdmin() && $user != null && !$user->isInstructor($this->site)) {
            return view('noaccess');
        }
        if ($sitepage->url != null && strlen($sitepage->url) > 5 && strcmp($sitepage->url, 'http') != 0) {
            return redirect($sitepage->url);
        }
        $nodata = null;
        return $this->prepareAndReturnView($sitepage, $nodata, $user, $request);
    }

    /**
     * This is the method that is called when a user clicks on a link to edit a site page's data 
     * using that pages' form
     */
    public function editSitePageData(Request $request, $section, $dataid)
    {
        if ($section == 'favicon.ico') {
            abort(404);
            return null;
        }
        $user = Auth::user() ?? null;
        if ($user == null) {
            \App\Http\Middleware\UserPageAccess::authorizeUser($request);
            if ($user == null) {
                Auth::logout();
                session()->flash('status', config('constants.LOGIN_AGAIN'));
                return redirect('/login');
            }
        }
        //make sure user has access to edit
        if (!$this->userService->isUserOnTeam($user)) {
            info('user is not on team to edit site page data ' . $user->id);
            abort(404);
        }
        //get record id'd by dataid
        $data = SitePageData::where('id', $dataid)
            ->firstOr(function () {
                return null;
            });
        if ($data == null) {
            info('data id not found in site page data table. ' . $dataid);
            abort(404);
        }

        //get view id'd in data record
        $sitepage = SitePages::where('id', $data->fk_site_page_id)
            ->where('section', $section)
            ->firstOr(function () {
                return null;
            });

        if ($sitepage == null) {
            info('page id not found from data ' . $data->fk_site_page_id);
            abort(404);
        }

        //put data in form
        return $this->prepareAndReturnView($sitepage, $data, $user, $request);
    }

    private function prepareAndReturnView($sitepage, $site_page_data, $user, $request)
    {
        // Handle different page types
        switch ($sitepage->type) {
            case 2: // S3 File
                $s3Content = $this->getS3PageContent($this->site->id, $sitepage->section);
                if (!empty($s3Content)) {
                    $sitepage->description = $s3Content;
                } else {
                    // Fall back to HTML content if S3 content is not found
                    $sitepage->description = $this->prepareTemplate($sitepage, $request->path());
                }
                break;

            case 3: // External URL
                if (!empty($sitepage->external_url)) {
                    return redirect()->away($sitepage->external_url);
                }
                // Fall through to HTML if no URL is provided
                $sitepage->description = $this->prepareTemplate($sitepage, $request->path());
                break;

            case 1: // HTML (default)
            default:
                $sitepage->description = $this->prepareTemplate($sitepage, $request->path());
                break;
        }

        $masterpage = $this->getMaster($sitepage);

        // Process template data if template is defined and contains [DATA] placeholder
        $placeholder = '[DATA]';
        if (
            $sitepage->template != null &&
            strlen($sitepage->template) > 0 &&
            strpos($sitepage->description ?? '', $placeholder) !== false
        ) {

            $page_content = '';
            if ($site_page_data == null) {
                // Multi-record result sets will be returned within the x-data attribute of the template
                $page_content = $this->sitePageService->getTemplateData($sitepage, $placeholder, $user, $this->site);
            } else {
                $template_data = SitePageTemplate::where('templatename', $sitepage->template)->first();
                if ($template_data) {
                    $jsonData = $this->sitePageService->processJSONData($site_page_data, $template_data, $this->site);
                    $page_content = str_replace($placeholder, $jsonData, $sitepage->description);
                }
            }

            if (!empty($page_content)) {
                $sitepage->description = $page_content;
            }
        }

        return view($sitepage->masterpage)
            ->with('sitePage', $sitepage)
            ->with('site', $this->site)
            ->with('page_short_url', '/page/' . $sitepage->section)
            ->with('masterPage', $masterpage);
    }
    /**
     * Show the app edit form 
     *
     * @return \Illuminate\Http\Response
     */
    public function editSitePages($siteid)
    {
        if ($siteid == 'favicon.ico') {
            abort(404);
            return null;
        }
        if (!Controller::userOkToViewPageByHost($this->userService)) {
            info('user not ok to view page. editSitePages ' . $siteid);
            return redirect('/login');
        }
        $masterpage = Controller::getMasterForSite($this->site);

        return view('sitepage.view-site-pages')
            ->with('siteid', $siteid)
            ->with('site', $this->site)
            ->with('masterPage', $masterpage);
    }

    public function saveSitePage(Request $request)
    {
        $this->sitePageService->saveSitePage($request);
        return redirect()->back();
    }


    /**
     * Livestream activity from AWS EventStream
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function livestream_activity(Request $request)
    {
        //ship this off to the logic that processes emails
        info('Livestream Activity: ' . $request->body);
        $receipient_user = \App\Models\User::where('id', 1)->first();
        $receipient_user->sendLivestreamNotification('Livestream Notification', $request->body, $receipient_user->email, $receipient_user->name);
    }

    public function giveToDonate()
    {

        return redirect()->to('/page/donate');
    }

    /**
     * a method used to store data from posted forms. 
     * the forms will be custom html stored in site_pages table and so this
     * will not depend on fixed form item names
     * the data will be stored in site_page_data table with siteid, data's key, json_data, and date created/updated
     */
    public function sitePageDataPost(Request $request, $siteid, $pageid)
    {
        // make sure the user has access to this site and page
        if (!Controller::userOkToViewPageByHost($this->userService)) {
            abort(403, 'Unauthorized action.');
        }
        $id = $request->input('id');
        $team_id = $request->input('team_id');
        $data_key = $request->input('data_key');

        $newOne = false;

        //if id is in the data, use it to find this record.
        $data = null;
        if ($id) {
            $data = SitePageData::where('id', $id)->first(); // Change from get() to first()
        } elseif ($data_key) {
            $data = SitePageData::where('fk_site_page_id', $pageid)->where('data_key', $data_key)->first();
        }

        if ($data_key == NULL) {
            $data_key = Str::uuid();
            $newOne = true;
        }
        if ($data == null) {

            $data = SitePageData::create(['fk_site_page_id' => $pageid, 'data_key' => $data_key, 'fk_team_id' => $this->site->teams[0]->id]);
        } else {
            // make sure this page belongs to this site
            if ($data->fk_team_id != $team_id) {
                $message = 'No changes due to mismatch in team: ' . $team_id . ' ' . $data->fk_team_id;
                session()->flash('message', $message);
                //save nothing
                redirect()->back();
            }
        }
        //loop through the request and gather form input values to build json object
        $json = array();
        foreach ($request->all() as $key => $value) {
            if ($key != '_token' && $key != 'template' && $key != 'siteid' && $key != 'pageid') {
                $json[$key] = $value;
            }
        }
        $json = json_encode($json);
        $data->json_data = $json;
        $data->fk_team_id = $team_id;
        $data->save();

        if ($request['return_json'] == 'true') {

            //return all json with this pageid from site page data if that value is true
            $site_page = SitePages::find($pageid);
            $site = Site::find($siteid);
            $jsonData = $this->sitePageService->getTemplateDataJSON($site_page,  Auth::user() ?? null, $site);

            $message = 'success';
            return $this->sendResponse($jsonData, $message);
        }

        //page didn't request json return, redirect back with a message.
        $page = SitePages::find($pageid);
        if ($newOne) {
            $message = 'A new ' . $page->section . ' record has been added';
        } else {
            $message = 'A ' . $page->section . ' record has been updated';
        }
        if (SitePages::pageNotificationsRequested($pageid)) {
            //user wants notifications when this data changes
            $user = \Auth::user();
            $user->sendDataUpdated($user, $this->site);
        }
        session()->flash('message', $message);
        return redirect()->back();
    }

    public function lateTemplateData(Request $request)
    {
        $pageid = $request['pageid'];
        // make sure the user has access to this site and page
        if (!Controller::userOkToViewPageByHost($this->userService)) {
            info('no access for this user. late template data: ' . $pageid);
            abort(403, 'Unauthorized action.');
        }

        $user = Auth::user() ?? null;
        $sitepage = SitePages::where('fk_site_id', $this->site->id)->where('id', $pageid)->first();

        if ($sitepage == null) {
            Log::info('lateTemplateData, page not found. using system welcome: ');
            return json_encode(['data' => '']);
        }

        if (($sitepage->requiresAuthentication() && $user == null) ||
            ($user != null && !$this->userService->isUserOnTeam($user))
        ) {
            if ($user == null) {
                \App\Http\Middleware\UserPageAccess::authorizeUser($request);
                $user = Auth::user() ?? null;
                json_encode(['data' => '']);
            }
            if (($user != null && !$this->userService->isUserOnTeam($user))) {
                json_encode(['data' => '']);
            }
        }
        //if the page requires admin, and the user isn't an admin, inform them no access to this page
        if ($sitepage->pageRequiresAdmin() && $user != null && !$user->isInstructor($this->site)) {
            return json_encode(['data' => '']);
        }

        if ($sitepage->template != null && strlen($sitepage->template) > 0) {
            $json_data = $this->sitePageService->getTemplateDataJSON($sitepage, $user, $this->site);
            return  $json_data;
        }

        return json_encode(['data' => '']);
    }

    public function readTsvIntoSitePageData(Request $request)
    {

        $pageid = $request['pageid'];
        // todo update this code to take a passed in file. for now, just use data.tsv
        $file = fopen('data.tsv', 'r');

        //ToDo: check if this site page has a data import defined.
        // if no data import defined then iterate the tabs and
        // do the key value pair thing as seen below

        // Loop through each line of the file
        while (($line = fgets($file)) !== false) {
            $data_key = Str::uuid();
            $page = SitePageData::create(['fk_site_page_id' => $pageid, 'data_key' => $data_key]);
            // Split the line into an array of values
            $values = explode("\t", $line);

            $json = array();
            foreach ($values as $key => $value) {
                $json["column$key"] = $value;
            }

            $json = json_encode($json);
            // info($json);
            $page->json_data = $json;
            $page->save();
        }

        // Close the file
        fclose($file);
    }

    /*/v1/sites/" + n.site_name + "/page_views*/
    public function pageView(Request $request, $site_name)
    {
        // Get the page ID from the request
        $page_name = json_encode($request->input('page_view_attributes'));
        // info('request vars: '. json_encode($request->all()));

        // Create a new PageView instance
        $pageView = new PageView();

        // Set the page ID and site ID for the page view
        $pageView->page_name = $page_name;
        $pageView->site_name = $site_name;

        // Save the page view to the database
        $pageView->save();

        // Return a response indicating success
        return response()->json(['message' => 'OK'], 200);
    }

    public function editSitePageJsonData($siteId, $sitePageId)
    {
        // make sure the user has access to this site and page
        if (!Controller::userOkToViewPageByHost($this->userService)) {
            info('no access for this user. editSitePageJsonData: ' .  $sitePageId);
            abort(403, 'Unauthorized action.');
        }

        $sitePage = SitePages::findOrFail($sitePageId);
        $sitePageData = SitePageData::where('fk_site_page_id', $sitePageId)->first();
        // Check if the site page data is null
        if (is_null($sitePageData)) {
            // Set a session flash message
            session()->flash('message', 'The page has no data defined for it.');

            // Redirect back to the previous page with the flash message
            return redirect()->back();
        }
        $jsonData = json_decode($sitePageData->json_data, true);

        return view('sitepage.edit-site-page-json-data', [
            'siteId' => $siteId,
            'sitePage' => $sitePage,
            'sitePageDataid' => $sitePageData->id,
            'jsonData' => $jsonData ?? []
        ]);
    }

    public function updateSitePageJsonData(Request $request, $siteId, $sitePageDataId)
    {
        // make sure the user has access to this site and page
        if (!Controller::userOkToViewPageByHost($this->userService)) {
            info('no access for this user. updateSitePageJsonData: $sitePageDataId ' .  $sitePageDataId);
            abort(403, 'Unauthorized action.');
        }

        $sitePageData = SitePageData::findOrFail($sitePageDataId);
        $updatedJsonData = $request->input('json_data');

        // Ensure the updated data is encoded back to JSON
        $sitePageData->json_data = json_encode($updatedJsonData);
        $sitePageData->save();

        return redirect()->route('sitepages.editSitePageJsonData', [$siteId, $sitePageData->fk_site_page_id])
            ->with('success', 'Site page json data updated successfully!');
    }

    // Add a new method to handle the deletion of a site page data item
    public function deleteSitePageJsonData($siteId, $sitePageId, $dataId)
    {
        // Find the site page data item by ID and delete it
        $sitePageData = SitePageData::find($dataId);
        if ($sitePageData) {
            $sitePageData->delete();
        }

        // Redirect back to the edit page with a success message
        return redirect()->route('sitepages.edit-site-page-json-data', ['siteId' => $siteId, 'sitePageId' => $sitePageId])
            ->with('success', 'Item deleted successfully.');
    }

    /**
     * Proxy a request to a remote Node.js server
     * 
     * This method forwards HTTP requests to a Node.js server running at pwa_server_url
     * and returns the response to the client. This allows Prasso to act as a reverse proxy
     * for PWA applications without requiring Apache vhost configuration.
     * 
     * @param string $serverUrl The base URL of the Node.js server (e.g., http://localhost:3001)
     * @param string $path The request path (e.g., /about)
     * @param Request $request The incoming HTTP request
     * @return \Illuminate\Http\Response The proxied response
     * @throws \Exception If the proxy request fails
     */
    private function proxyRequestToServer($serverUrl, $path, Request $request)
    {
        // Ensure serverUrl doesn't have trailing slash
        $serverUrl = rtrim($serverUrl, '/');
        
        // Build the full URL to proxy to
        $proxyUrl = $serverUrl . $path;
        
        // Add query string if present
        if ($request->getQueryString()) {
            $proxyUrl .= '?' . $request->getQueryString();
        }
        
        try {
            // Determine the HTTP method
            $method = strtolower($request->getMethod());
            
            // Build request headers to forward
            $headers = [];
            foreach ($request->headers->all() as $key => $value) {
                // Skip headers that shouldn't be forwarded
                if (!in_array(strtolower($key), ['host', 'connection', 'content-length'])) {
                    $headers[$key] = $value[0] ?? implode(',', $value);
                }
            }
            
            // Make the proxy request
            $httpRequest = Http::withHeaders($headers)
                ->timeout(30)
                ->withoutRedirecting();
            
            // Add body for POST/PUT/PATCH requests
            if (in_array($method, ['post', 'put', 'patch'])) {
                $body = $request->getContent();
                if ($body) {
                    $httpRequest = $httpRequest->withBody($body, $request->header('Content-Type'));
                }
            }
            
            // Execute the proxy request (use send for broader Laravel version compatibility)
            $response = $httpRequest->send(strtoupper($method), $proxyUrl);
            
            // Build response to return to client
            return response(
                $response->body(),
                $response->status(),
                $response->headers()
            );
        } catch (\Exception $e) {
            Log::error("Proxy request failed for URL {$proxyUrl}: {$e->getMessage()}");
            throw $e;
        }
    }
}
