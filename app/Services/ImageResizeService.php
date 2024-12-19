<?php

namespace App\Services;

use Intervention\Image\Facades\Image;

class ImageResizeService
{
    const MAX_FILE_SIZE = 2048; 

    public function needsResize($file)
    {
        return $file->getSize() > (self::MAX_FILE_SIZE * 1024);
    }

    public function resize($file)
    {
        $image = Image::make($file);
        $originalWidth = $image->width();
        $originalHeight = $image->height();

        // Start with 80% quality
        $quality = 80;
        
        do {
            // Create a temporary file to check the size
            $tempPath = sys_get_temp_dir() . '/' . uniqid('resize_') . '.' . $file->getClientOriginalExtension();
            
            // Resize maintaining aspect ratio if image is very large
            if ($originalWidth > 2048 || $originalHeight > 2048) {
                $image->resize(2048, 2048, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            
            // Save with current quality
            $image->save($tempPath, $quality);
            
            // Check new file size
            $newSize = filesize($tempPath);
            
            // Remove temporary file
            unlink($tempPath);
            
            // Reduce quality if still too large
            if ($newSize > (self::MAX_FILE_SIZE * 1024)) {
                $quality -= 10;
            }
        } while ($newSize > (self::MAX_FILE_SIZE * 1024) && $quality > 20);

        // If we still couldn't get it under max size with quality 20%, return null
        if ($newSize > (self::MAX_FILE_SIZE * 1024)) {
            return null;
        }

        // Create final resized image
        $finalPath = sys_get_temp_dir() . '/' . uniqid('final_') . '.' . $file->getClientOriginalExtension();
        $image->save($finalPath, $quality);
        
        return $finalPath;
    }
}
