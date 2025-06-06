<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Aws\S3\S3Client;
use Aws\Laravel\AwsServiceProvider as BaseAwsServiceProvider;

class AwsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Set the SSL certificate path globally for all cURL requests
        $certPath = storage_path('cacert.pem');
        if (file_exists($certPath)) {
            putenv('CURL_CA_BUNDLE=' . $certPath);
        }
        
        $this->app->singleton('aws', function ($app) {
            $config = $app->make('config')->get('aws', []);
            
            return new \Aws\Sdk($config);
        });
        
        $this->app->singleton('s3', function ($app) use ($certPath) {
            // Get S3 configuration from filesystems.php
            $s3Config = [
                'version' => 'latest',
                'region' => config('filesystems.disks.s3.region'),
                'credentials' => [
                    'key' => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
                'http' => [
                    'verify' => file_exists($certPath) ? $certPath : false
                ]
            ];
            
            return new S3Client($s3Config);
        });
        
        // Override the S3 client in Laravel's storage system
        $this->app->bind('League\Flysystem\AwsS3V3\AwsS3V3Adapter', function ($app) {
            $client = $app->make('s3');
            $adapter = new \League\Flysystem\AwsS3V3\AwsS3V3Adapter(
                $client,
                config('filesystems.disks.s3.bucket'),
                config('filesystems.disks.s3.root', '')
            );
            return $adapter;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
