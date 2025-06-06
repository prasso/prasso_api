<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class S3Helper
{
    /**
     * Upload a file to S3 with proper SSL certificate handling
     * 
     * @param string $path The path where the file will be stored in S3
     * @param mixed $contents The file contents to upload
     * @param array $options Additional options for the upload
     * @return bool Whether the upload was successful
     */
    public static function upload($path, $contents, $options = [])
    {
        try {
            // Use the Laravel Storage facade which will use our configured S3 disk
            // The S3 disk is already configured to use the CA certificate bundle
            $result = Storage::disk('s3')->put($path, $contents, $options);
            
            if ($result) {
                Log::info('S3 upload successful for: ' . $path);
            } else {
                Log::error('S3 upload failed for: ' . $path);
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('S3 upload exception: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ensure the CA certificate bundle exists
     * 
     * @return bool Whether the CA certificate bundle exists
     */
    public static function ensureCertificateExists()
    {
        $certPath = base_path('cacert.pem');
        
        if (!file_exists($certPath)) {
            Log::warning('CA certificate bundle not found at: ' . $certPath);
            return false;
        }
        
        return true;
    }
}
