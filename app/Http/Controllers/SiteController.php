<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\faqs_storage;
use App\Services\GithubRepositoryService;
use App\Services\GithubRepositoryCreationService;
use Auth;
use App\Http\Requests\SiteRequest;

class SiteController extends BaseController
{
    public function __construct(SiteRequest $request)
    {
        parent::__construct( $request);
    }
    
    /**
     * Deploy the GitHub repository for a site
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deployGithubRepository($id)
    {
        $site = Site::findOrFail($id);
        
        if (empty($site->github_repository)) {
            return redirect()->back()->with('error', 'This site does not have a GitHub repository configured.');
        }
        
        try {
            // Use the GitHub repository service to handle the deployment
            $githubService = new GithubRepositoryService();
            $result = $githubService->deploy($site);
            
            // Log the deployment success
            Log::info('Successfully deployed GitHub repository for site: ' . $site->site_name . ' (' . $site->github_repository . ')');
            
            // Create a detailed success message based on the action taken
            $action = $result['action'] === 'clone' ? 'cloned' : 'updated with latest changes';
            $message = "GitHub repository {$action} successfully.";
            $message .= "\nRepository: {$result['repository']}";
            $message .= "\nDeployment path: {$result['deployment_path']}";
            
            // Add git output if available
            if (!empty($result['output'])) {
                $message .= "\n\nOutput:\n" . implode("\n", $result['output']);
            }
            
            // Return success message
            return redirect()->back()->with('message', $message);
        } catch (\Exception $e) {
            Log::error('Error deploying GitHub repository: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to deploy GitHub repository: ' . $e->getMessage());
        }
    }
    
    /**
     * Create a new GitHub repository from a folder
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createGithubRepository(Request $request)
    {
        // Validate the request
        if ($request->hasFile('files')) {
            // File upload approach
            $request->validate([
                'site_id' => 'required|exists:sites,id',
                'folder_path' => 'required|string',
                'repository_name' => 'required|string',
                'files' => 'required|array',
                'files.*' => 'file',
            ]);
        } else {
            // Traditional approach
            $request->validate([
                'site_id' => 'required|exists:sites,id',
                'folder_path' => 'required|string',
                'repository_name' => 'required|string',
            ]);
        }
        
        $siteId = $request->input('site_id');
        $folderPath = $request->input('folder_path');
        $repositoryName = $request->input('repository_name');
        
        try {
            // Create a temporary directory for uploaded files if needed
            if ($request->hasFile('files')) {
                // Create a unique temporary directory
                $tempDir = storage_path('app/temp/github_repos/' . uniqid($repositoryName . '_'));
                
                // Process the uploaded files
                $this->processUploadedFiles($request->file('files'), $tempDir);
                
                // Use the temporary directory as the folder path
                $folderPath = $tempDir;
            }
            
            // Get GitHub credentials from config or environment
            $githubToken = config('services.github.token') ?? env('GITHUB_TOKEN');
            $githubUsername = config('services.github.username') ?? env('GITHUB_USERNAME');
            
            // Log credential status for debugging
            \Illuminate\Support\Facades\Log::info('GitHub Credentials Status', [
                'token_exists' => !empty($githubToken),
                'username_exists' => !empty($githubUsername)
            ]);
            
            // Create the GitHub repository explicitly passing credentials
            $githubService = new GithubRepositoryCreationService();
            $result = $githubService->createFromFolder(
                $folderPath,
                $repositoryName,
                $githubToken,
                $githubUsername
            );
            
            // Update the site with the new GitHub repository
            $site = Site::findOrFail($siteId);
            $site->github_repository = $result['repository_path'];
            $site->save();
            
            // Log the repository creation
            Log::info('Successfully created GitHub repository for site: ' . $site->site_name . ' (' . $result['repository_path'] . ')');
            
            // Create a detailed success message
            $message = "GitHub repository created successfully.";
            $message .= "\nRepository: {$result['repository_path']}";
            $message .= "\nSource folder: {$result['folder_path']}";
            
            // Add steps and output if available
            if (!empty($result['steps'])) {
                $message .= "\n\nSteps:\n" . implode("\n", $result['steps']);
            }
            
            if (!empty($result['output'])) {
                $message .= "\n\nOutput:\n" . implode("\n", $result['output']);
            }
            
            // Return success response
            return response()->json([
                'success' => true,
                'message' => $message,
                'repository_path' => $result['repository_path'],
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating GitHub repository: ' . $e->getMessage());
            
            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'Failed to create GitHub repository: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $user = Auth::user() ?? null;   
        if ($user == null || $user->currentTeam == null){
            Auth::logout();
            session()->flash('status',config('constants.LOGIN_AGAIN'));
            return redirect('/login');
        }
        $sites = Site::latest()->paginate(15);
        $team = $user->currentTeam;
        $team_selection = $team->pluck('name','id');
        
        return view('sites.show', compact('sites'))
            ->with('i', (request()->input('page', 1) - 1) * 5)
            ->with('team_selection', $team_selection);
    }

    /**
     * FAQ show what we have
     *
     */
    public function seeFaqs()
    {
        $faqs = faqs_storage::latest()->paginate(15);

        return view('sites.faqs', compact('faqs'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * FAQ question,
     * eventually - send the message via Twilio to the customer support folk
     * atm txt to me
     *
     */
    /**
     * Process uploaded files and save them to the specified directory
     * preserving the folder structure
     *
     * @param array $files The uploaded files
     * @param string $targetDir The target directory
     * @return void
     */
    private function processUploadedFiles($files, $targetDir)
    {
        // Create the target directory if it doesn't exist
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        // Keep track of the root folder name to ensure all files are within it
        $rootFolderName = null;
        
        // Log the file information for debugging
        \Illuminate\Support\Facades\Log::info("Processing " . count($files) . " files");
        
        foreach ($files as $file) {
            // The original path might be in the file name or in a custom header
            $originalPath = $file->getClientOriginalName();
            
            // Log the original path for debugging
            \Illuminate\Support\Facades\Log::info("Original file path: {$originalPath}");
            
            // Check if we have a full path with webkitRelativePath format (folder/file.ext)
            if (strpos($originalPath, '/') !== false) {
                // Extract the path parts
                $pathParts = explode('/', $originalPath);
                
                // The first part should be the root folder name
                if ($rootFolderName === null) {
                    $rootFolderName = $pathParts[0];
                }
                
                $filename = array_pop($pathParts); // Remove and get the filename
                $relativeDir = implode('/', $pathParts);
                
                // Create the subdirectory if needed
                $subDir = $targetDir . '/' . $relativeDir;
                if (!file_exists($subDir)) {
                    mkdir($subDir, 0755, true);
                }
                
                // Save the file to the correct location
                $file->move($subDir, $filename);
                
                // Log the file save location for debugging
                \Illuminate\Support\Facades\Log::info("Saved file to: {$subDir}/{$filename}");
            } else {
                // Try to get the path from the request
                $relativePath = request()->input('file_paths.' . $file->getClientOriginalName());
                
                if ($relativePath) {
                    // Extract the path parts
                    $pathParts = explode('/', $relativePath);
                    $filename = array_pop($pathParts); // Remove and get the filename
                    $relativeDir = implode('/', $pathParts);
                    
                    // Create the subdirectory if needed
                    $subDir = $targetDir . '/' . $relativeDir;
                    if (!file_exists($subDir)) {
                        mkdir($subDir, 0755, true);
                    }
                    
                    // Save the file to the correct location
                    $file->move($subDir, $filename);
                    
                    // Log the file save location for debugging
                    \Illuminate\Support\Facades\Log::info("Saved file with path from request to: {$subDir}/{$filename}");
                } else {
                    // If we don't have a path structure, just save directly to the target dir
                    // This is a fallback and shouldn't normally happen with folder selection
                    $file->move($targetDir, $originalPath);
                    
                    // Log the fallback file save for debugging
                    \Illuminate\Support\Facades\Log::info("Saved file directly to target dir: {$targetDir}/{$originalPath}");
                }
            }
        }
        
        // Add .gitkeep files to empty directories to preserve folder structure
        $this->preserveEmptyDirectories($targetDir);
        
        // Log information about the processed files
        \Illuminate\Support\Facades\Log::info("Processed " . count($files) . " files to {$targetDir}");
        
        return $targetDir;
    }
    
    /**
     * Add .gitkeep files to empty directories to preserve folder structure
     *
     * @param string $basePath Base path to start from
     */
    private function preserveEmptyDirectories($basePath)
    {
        $directories = new \RecursiveDirectoryIterator($basePath, \RecursiveDirectoryIterator::SKIP_DOTS);
        $recursiveIterator = new \RecursiveIteratorIterator($directories, \RecursiveIteratorIterator::SELF_FIRST);
        
        foreach ($recursiveIterator as $item) {
            if ($item->isDir()) {
                $dirPath = $item->getPathname();
                
                // Skip .git directory
                if (strpos($dirPath, '/.git') !== false) {
                    continue;
                }
                
                // Check if directory is empty
                $isEmpty = true;
                $dirContents = scandir($dirPath);
                foreach ($dirContents as $content) {
                    if ($content != '.' && $content != '..') {
                        $isEmpty = false;
                        break;
                    }
                }
                
                // Add .gitkeep file to empty directory
                if ($isEmpty) {
                    $gitkeepPath = $dirPath . '/.gitkeep';
                    file_put_contents($gitkeepPath, '');
                    \Illuminate\Support\Facades\Log::info("Added .gitkeep to empty directory: {$dirPath}");
                }
            }
        }
    }
    
    public function processQuestion(Request $request, Site $site)
    {
        $this->validate($request, [
            'question' => 'required'
        ]);
        $this->serverKey = config('app.firebase_server_key');
        $question = $request->question;
        $email = $request->email;

        $admin_user = \App\Models\User::where('email','bcp@faxt.com')->first();

        $data = [
            "to" => $admin_user->pn_token,
            "notification" =>
                [
                    "title" => 'Prasso FAQ Request',
                    "body" => $question.' and Reply To: '.$email,
                    "icon" => url($site->logo_image)
                ],
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $this->serverKey,
            'Content-Type: application/json',
        ];
   
        $url='https://fcm.googleapis.com/fcm/send';
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization'=> 'key='. $this->serverKey,
        ])->post($url, $data);

        return redirect('/page/faqs')->with('message', 'Your question was sent.'); 

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $team = Auth::user()->currentTeam;
        $team_selection = $team->pluck('name','id');
        return view('sites.create-or-edit')
            ->with('team_selection', $team_selection);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SiteRequest $request)
    {
        $request->validated();

        Site::create($request->all());

        return redirect()->route('sites.index')
            ->with('success', 'Site created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function show(Site $site)
    {
        return view('sites.show', compact('Site'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function update(SiteRequest $request, Site $site)
    {
        $request->validated();
        $site->update($request->all());

        return redirect()->route('sites.index')
            ->with('success', 'Site updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function destroy(Site $site)
    {
        $site->delete();

        return redirect()->route('sites.index')
            ->with('success', 'Site deleted successfully');
    }



}
