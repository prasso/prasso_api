<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GithubRepositoryService
{
    /**
     * Deploy a GitHub repository for a site
     *
     * @param Site $site
     * @return array Information about the deployment
     */
    public function deploy(Site $site)
    {
        if (empty($site->github_repository)) {
            throw new \Exception('No GitHub repository configured for this site');
        }

        // Parse the repository name (username/repo)
        $repoPath = $site->github_repository;
        
        // Extract repository name from the path (e.g., "username/repo" -> "repo")
        $repoName = explode('/', $repoPath)[1] ?? $repoPath;
        
        // Define the target directory in public/hosted_sites/{repository_name}
        $targetDir = public_path('hosted_sites/' . $repoName);
        
        try {
            // Log the deployment start
            Log::info("Starting GitHub repository deployment for site {$site->id} ({$repoPath})");
            
            $result = [
                'action' => '',
                'output' => [],
                'deployment_path' => '',
                'repository' => $repoPath
            ];
            
            // Check if the repository directory already exists
            if (File::exists($targetDir)) {
                // Repository exists, perform a git pull
                Log::info("Repository folder exists, performing git pull in {$targetDir}");
                $result['action'] = 'pull';
                
                // Change to the repository directory and execute git pull
                $currentDir = getcwd();
                chdir($targetDir);
                
                // First try with --ff-only which is safer but will fail if branches have diverged
                $output = [];
                $returnCode = 0;
                exec('git pull --ff-only 2>&1', $output, $returnCode);
                
                // If the fast-forward pull fails, try a more aggressive approach
                if ($returnCode !== 0) {
                    Log::warning("Fast-forward pull failed for site {$site->id}, trying reset approach");
                    
                    // Store the current branch name
                    $branchOutput = [];
                    exec('git branch --show-current 2>&1', $branchOutput, $branchReturnCode);
                    $currentBranch = $branchReturnCode === 0 ? trim($branchOutput[0]) : 'main';
                    
                    // Reset to origin and force pull
                    $resetOutput = [];
                    exec("git fetch origin 2>&1 && git reset --hard origin/{$currentBranch} 2>&1", $resetOutput, $resetReturnCode);
                    
                    if ($resetReturnCode === 0) {
                        $output = array_merge($output, ["Fast-forward failed, used reset instead"], $resetOutput);
                        $returnCode = 0; // Consider the operation successful
                    } else {
                        // If reset fails too, append the reset output to the original output
                        $output = array_merge($output, ["Reset approach also failed"], $resetOutput);
                    }
                }
                
                $result['output'] = $output;
                
                // Change back to the original directory
                chdir($currentDir);
                
                // Check if the pull was successful
                if ($returnCode !== 0) {
                    $errorMessage = implode("\n", $output);
                    Log::error("Git pull failed for site {$site->id}: {$errorMessage}");
                    throw new \Exception("Git pull failed: {$errorMessage}");
                }
                
                Log::info("Successfully pulled latest changes for site {$site->id}");
            } else {
                // Repository doesn't exist, perform a git clone
                Log::info("Repository folder doesn't exist, cloning to {$targetDir}");
                $result['action'] = 'clone';
                
                // Create the parent directory if it doesn't exist
                $parentDir = dirname($targetDir);
                if (!File::exists($parentDir)) {
                    File::makeDirectory($parentDir, 0755, true);
                }
                
                // Execute git clone
                $output = [];
                $returnCode = 0;
                $gitUrl = "https://github.com/{$repoPath}.git";
                exec("git clone {$gitUrl} {$targetDir} 2>&1", $output, $returnCode);
                $result['output'] = $output;
                
                // Check if the clone was successful
                if ($returnCode !== 0) {
                    $errorMessage = implode("\n", $output);
                    Log::error("Git clone failed for site {$site->id}: {$errorMessage}");
                    throw new \Exception("Git clone failed: {$errorMessage}");
                }
                
                Log::info("Successfully cloned repository for site {$site->id}");
            }
            
            // Update the site with the deployment path if needed
            $deploymentPath = "/hosted_sites/{$repoName}";
            $result['deployment_path'] = $deploymentPath;
            
            if ($site->deployment_path !== $deploymentPath) {
                $site->deployment_path = $deploymentPath;
                $site->save();
                
                Log::info("Updated site {$site->id} with deployment path: {$deploymentPath}");
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error("Error deploying GitHub repository for site {$site->id}: " . $e->getMessage());
            throw $e;
        }
    }
}
