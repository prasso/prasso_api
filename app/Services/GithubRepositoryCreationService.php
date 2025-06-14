<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use GuzzleHttp\Promise;

class GithubRepositoryCreationService
{
    /**
     * Create a new GitHub repository from a local folder
     *
     * @param string $folderPath Path to the local folder
     * @param string $repositoryName Name for the new repository
     * @param string $githubToken GitHub personal access token
     * @param string $githubUsername GitHub username
     * @return array Information about the created repository
     */
    public function createFromFolder($folderPath, $repositoryName, $githubToken = null, $githubUsername = null)
    {
        // Use environment variables if parameters are not provided
        $githubToken = $githubToken ?? env('GITHUB_TOKEN');
        $githubUsername = $githubUsername ?? env('GITHUB_USERNAME');
        $githubOrg = env('GITHUB_ORGANIZATION', $githubUsername);
        
        // Validate inputs
        if (empty($folderPath) || !File::exists($folderPath)) {
            throw new \Exception("Source folder does not exist: {$folderPath}");
        }
        
        if (empty($repositoryName)) {
            throw new \Exception("Repository name is required");
        }
        
        if (empty($githubToken) || empty($githubUsername)) {
            throw new \Exception("GitHub credentials are required. Please set GITHUB_TOKEN and GITHUB_USERNAME in your .env file.");
        }
        
        $result = [
            'repository_name' => $repositoryName,
            'repository_path' => "{$githubOrg}/{$repositoryName}",
            'folder_path' => $folderPath,
            'steps' => [],
            'output' => []
        ];
        
        try {
            // Check if the folder is already a git repository
            $isGitRepo = $this->isGitRepository($folderPath);
            
            if ($isGitRepo) {
                $result['steps'][] = "Folder is already a git repository";
                $result['output'][] = "Found existing git repository";
                
                // Check if it has a remote origin
                $remoteOrigin = $this->getRemoteOrigin($folderPath);
                
                if (!empty($remoteOrigin)) {
                    $result['steps'][] = "Repository already has a remote origin";
                    $result['output'][] = "Remote origin: {$remoteOrigin}";
                    
                    // Extract repository details from remote origin
                    $repoDetails = $this->extractRepositoryDetailsFromRemote($remoteOrigin);
                    if ($repoDetails) {
                        $result['repository_path'] = $repoDetails['full_name'];
                        $result['repository_name'] = $repoDetails['name'];
                        $result['success'] = true;
                        $result['message'] = "Found existing GitHub repository";
                        $result['html_url'] = $repoDetails['html_url'];
                        return $result;
                    }
                }
            }
            
            // If not a git repo or doesn't have a remote origin, create a new repository
            $result['steps'][] = "Creating repository on GitHub: {$repositoryName}";
            $repoCreated = $this->createGithubRepository($repositoryName, $githubToken, $githubOrg);
            $result['output'][] = "Repository created: {$repoCreated['html_url']}";
            $result['html_url'] = $repoCreated['html_url'];
            
            // Initialize git repository if needed
            if (!$isGitRepo) {
                $result['steps'][] = "Initializing git repository in local folder";
                $this->initializeLocalRepository($folderPath, $result);
            }
            
            // Step 3: Add remote origin
            $result['steps'][] = "Adding remote origin";
            $remoteUrl = "https://{$githubUsername}:{$githubToken}@github.com/{$githubOrg}/{$repositoryName}.git";
            $this->addRemoteOrigin($folderPath, $remoteUrl, $result);
            
            // Step 4: Add all files
            $result['steps'][] = "Adding files to repository";
            $this->addFiles($folderPath, $result);
            
            // Step 5: Commit files
            $result['steps'][] = "Committing files";
            $this->commitFiles($folderPath, "Initial commit from Prasso", $result);
            
            // Step 6: Push to GitHub
            $result['steps'][] = "Pushing to GitHub";
            $this->pushToGithub($folderPath, $result);
            
            $result['success'] = true;
            $result['message'] = "Successfully created GitHub repository from folder";
            
            return $result;
        } catch (\Exception $e) {
            Log::error("Error creating GitHub repository: " . $e->getMessage());
            $result['success'] = false;
            $result['error'] = $e->getMessage();
            throw $e;
        }
    }
    
    /**
     * Create a new GitHub repository
     *
     * @param string $name Repository name
     * @param string $token GitHub personal access token
     * @param string $org GitHub organization name (optional)
     * @return array Repository data
     */
    private function createGithubRepository($name, $token, $org = null)
    {
        $headers = [
            'Authorization' => "token {$token}",
            'Accept' => 'application/vnd.github.v3+json',
        ];
        
        // Determine if we're creating in an organization or user account
        $endpoint = !empty($org) && $org !== env('GITHUB_USERNAME') 
            ? "https://api.github.com/orgs/{$org}/repos" 
            : 'https://api.github.com/user/repos';
        
        $response = Http::withHeaders($headers)->post($endpoint, [
            'name' => $name,
            'private' => false,
            'auto_init' => false,
            'description' => 'Created from Prasso',
        ]);
        
        if (!$response->successful()) {
            throw new \Exception("Failed to create GitHub repository: " . $response->body());
        }
        
        return $response->json();
    }
    
    /**
     * Initialize a git repository in the local folder
     *
     * @param string $folderPath Path to the local folder
     * @param array &$result Result array to update with output
     */
    private function initializeLocalRepository($folderPath, &$result)
    {
        $currentDir = getcwd();
        chdir($folderPath);
        
        // Check if git is already initialized
        if (File::exists("{$folderPath}/.git")) {
            $result['output'][] = "Git repository already initialized";
        } else {
            $output = [];
            $returnCode = 0;
            exec('git init 2>&1', $output, $returnCode);
            
            if ($returnCode !== 0) {
                chdir($currentDir);
                throw new \Exception("Failed to initialize git repository: " . implode("\n", $output));
            }
            
            $result['output'][] = "Git repository initialized";
        }
        
        chdir($currentDir);
    }
    
    /**
     * Add remote origin to the local repository
     *
     * @param string $folderPath Path to the local folder
     * @param string $remoteUrl Remote URL
     * @param array &$result Result array to update with output
     */
    private function addRemoteOrigin($folderPath, $remoteUrl, &$result)
    {
        $currentDir = getcwd();
        chdir($folderPath);
        
        $output = [];
        $returnCode = 0;
        
        // Check if remote origin already exists
        exec('git remote 2>&1', $output, $returnCode);
        
        if (in_array('origin', $output)) {
            // Remove existing origin
            exec('git remote remove origin 2>&1', $output, $returnCode);
        }
        
        // Add new origin
        $output = [];
        $returnCode = 0;
        exec("git remote add origin {$remoteUrl} 2>&1", $output, $returnCode);
        
        if ($returnCode !== 0) {
            chdir($currentDir);
            throw new \Exception("Failed to add remote origin: " . implode("\n", $output));
        }
        
        $result['output'][] = "Remote origin added";
        chdir($currentDir);
    }
    
    /**
     * Add all files to the git repository
     *
     * @param string $folderPath Path to the local folder
     * @param array &$result Result array to update with output
     */
    private function addFiles($folderPath, &$result)
    {
        $currentDir = getcwd();
        chdir($folderPath);
        
        // First, add .gitkeep files to empty directories to preserve folder structure
        $this->preserveEmptyDirectories($folderPath);
        
        $output = [];
        $returnCode = 0;
        exec('git add . 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            chdir($currentDir);
            throw new \Exception("Failed to add files: " . implode("\n", $output));
        }
        
        $result['output'][] = "Files added to git";
        chdir($currentDir);
    }
    
    /**
     * Commit files to the git repository
     *
     * @param string $folderPath Path to the local folder
     * @param string $message Commit message
     * @param array &$result Result array to update with output
     */
    private function commitFiles($folderPath, $message, &$result)
    {
        $currentDir = getcwd();
        chdir($folderPath);
        
        // Configure git user if not already configured
        $this->configureGitUser($folderPath);
        
        $output = [];
        $returnCode = 0;
        exec("git commit -m \"{$message}\" 2>&1", $output, $returnCode);
        
        // Return code 1 might mean nothing to commit
        if ($returnCode !== 0 && !$this->isNothingToCommitError($output)) {
            chdir($currentDir);
            throw new \Exception("Failed to commit files: " . implode("\n", $output));
        }
        
        $result['output'][] = "Files committed: " . implode("\n", $output);
        chdir($currentDir);
    }
    
    /**
     * Push to GitHub
     *
     * @param string $folderPath Path to the local folder
     * @param array &$result Result array to update with output
     */
    private function pushToGithub($folderPath, &$result)
    {
        $currentDir = getcwd();
        chdir($folderPath);
        
        $output = [];
        $returnCode = 0;
        exec('git push -u origin master 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            // Try main branch if master fails
            $output = [];
            $returnCode = 0;
            exec('git push -u origin main 2>&1', $output, $returnCode);
            
            if ($returnCode !== 0) {
                chdir($currentDir);
                throw new \Exception("Failed to push to GitHub: " . implode("\n", $output));
            }
        }
        
        $result['output'][] = "Pushed to GitHub: " . implode("\n", $output);
        chdir($currentDir);
    }
    
    /**
     * Configure git user for the repository
     *
     * @param string $folderPath Path to the local folder
     */
    private function configureGitUser($folderPath)
    {
        // Check if user is configured
        $output = [];
        $returnCode = 0;
        exec('git config user.name 2>&1', $output, $returnCode);
        
        if (empty($output) || $returnCode !== 0) {
            exec('git config user.name "Prasso System" 2>&1');
            exec('git config user.email "system@prasso.io" 2>&1');
        }
    }
    
    /**
     * Check if the git error is just "nothing to commit"
     *
     * @param array $output Command output
     * @return bool True if it's a "nothing to commit" message
     */
    private function isNothingToCommitError($output)
    {
        $outputStr = implode(' ', $output);
        return (strpos($outputStr, 'nothing to commit') !== false);
    }
    
    /**
     * Check if a folder is a git repository
     *
     * @param string $folderPath Path to the folder
     * @return bool True if the folder is a git repository
     */
    private function isGitRepository($folderPath)
    {
        return File::exists("{$folderPath}/.git") && File::isDirectory("{$folderPath}/.git");
    }
    
    /**
     * Get the remote origin URL of a git repository
     *
     * @param string $folderPath Path to the git repository
     * @return string|null Remote origin URL or null if not found
     */
    private function getRemoteOrigin($folderPath)
    {
        $currentDir = getcwd();
        chdir($folderPath);
        
        $output = [];
        $returnCode = 0;
        exec('git remote get-url origin 2>&1', $output, $returnCode);
        
        chdir($currentDir);
        
        if ($returnCode !== 0 || empty($output)) {
            return null;
        }
        
        return trim($output[0]);
    }
    
    /**
     * Extract repository details from a remote URL
     *
     * @param string $remoteUrl Remote URL
     * @return array|null Repository details or null if extraction failed
     */
    private function extractRepositoryDetailsFromRemote($remoteUrl)
    {
        // Extract owner and repo name from URL
        // Handles formats like:
        // https://github.com/owner/repo.git
        // git@github.com:owner/repo.git
        // https://username:token@github.com/owner/repo.git
        
        $pattern = '/(?:github\.com[\/|:])([^\/]+)\/([^\/\.]+)(?:\.git)?$/i';
        if (preg_match($pattern, $remoteUrl, $matches)) {
            $owner = $matches[1];
            $repo = $matches[2];
            
            try {
                // Get repository details from GitHub API
                $token = env('GITHUB_TOKEN');
                $response = Http::withHeaders([
                    'Authorization' => "token {$token}",
                    'Accept' => 'application/vnd.github.v3+json',
                ])->get("https://api.github.com/repos/{$owner}/{$repo}");
                
                if ($response->successful()) {
                    return $response->json();
                }
                
                return [
                    'full_name' => "{$owner}/{$repo}",
                    'name' => $repo,
                    'html_url' => "https://github.com/{$owner}/{$repo}"
                ];
            } catch (\Exception $e) {
                Log::error("Error fetching repository details: " . $e->getMessage());
                return Promise::reject($e->getMessage());
            }
        }
        
        return null;
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
                }
            }
        }
    }
}
