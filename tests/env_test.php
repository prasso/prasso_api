<?php

// Simple script to check if environment variables are loading correctly

// Load environment variables
require_once __DIR__ . '/../vendor/autoload.php';

echo "Testing environment variables loading:\n\n";

// Check if dotenv is available
if (class_exists('Dotenv\Dotenv')) {
    try {
        // Load .env file and make variables available through putenv
        $dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . '/..');
        $dotenv->load();
        
        // Manually set the variables using putenv
        foreach ($_ENV as $key => $value) {
            putenv("$key=$value");
        }
        
        echo "✅ .env file loaded successfully\n";
    } catch (Exception $e) {
        echo "❌ Error loading .env file: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Dotenv class not found. Make sure you have vlucas/phpdotenv installed.\n";
}

// Check specific environment variables
echo "\nEnvironment variables:\n";
echo "GITHUB_USERNAME: " . (getenv('GITHUB_USERNAME') ?: 'Not set') . "\n";
echo "GITHUB_TOKEN: " . (getenv('GITHUB_TOKEN') ? 'Set (value hidden for security)' : 'Not set') . "\n";

// Check if Laravel's env helper function is available
if (function_exists('env')) {
    echo "\nUsing Laravel's env() function:\n";
    echo "GITHUB_USERNAME: " . (env('GITHUB_USERNAME') ?: 'Not set') . "\n";
    echo "GITHUB_TOKEN: " . (env('GITHUB_TOKEN') ? 'Set (value hidden for security)' : 'Not set') . "\n";
} else {
    echo "\nLaravel's env() function is not available in this context.\n";
}

// Helper function to mimic Laravel's env() function
function env($key, $default = null) {
    $value = getenv($key);
    return $value !== false ? $value : $default;
}
