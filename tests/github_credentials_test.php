<?php

// Simple script to test GitHub credentials from .env file

// Load environment variables
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . '/..');
$dotenv->load();

// Manually set the variables using putenv
foreach ($_ENV as $key => $value) {
    putenv("$key=$value");
}

// Get GitHub credentials from environment
$githubToken = env('GITHUB_TOKEN');
$githubUsername = env('GITHUB_USERNAME');

echo "Testing GitHub credentials for user: {$githubUsername}\n";

// Make a simple API call to GitHub to list repositories
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.github.com/user/repos?per_page=5");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: token {$githubToken}",
    "User-Agent: Prasso-API-Test",
    "Accept: application/vnd.github.v3+json"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Check if the request was successful
if ($httpCode === 200) {
    $repositories = json_decode($response, true);
    echo "✅ GitHub credentials are valid!\n";
    echo "Found " . count($repositories) . " repositories.\n";
    
    if (count($repositories) > 0) {
        echo "\nHere are some of your repositories:\n";
        foreach ($repositories as $index => $repo) {
            echo ($index + 1) . ". {$repo['name']} - {$repo['html_url']}\n";
            if ($index >= 4) break; // Show max 5 repos
        }
    }
} else {
    echo "❌ GitHub credentials are invalid or there was an error.\n";
    echo "HTTP Status Code: {$httpCode}\n";
    echo "Response: {$response}\n";
    
    // Check for common errors
    if ($httpCode === 401) {
        echo "\nAuthentication failed. Please check your GitHub token.\n";
    } elseif ($httpCode === 403) {
        echo "\nRate limit exceeded or insufficient permissions.\n";
        echo "Make sure your token has the 'repo' scope.\n";
    }
}

// Helper function to mimic Laravel's env() function
function env($key, $default = null) {
    $value = getenv($key);
    return $value !== false ? $value : $default;
}
