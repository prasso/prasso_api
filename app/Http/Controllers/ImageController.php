<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeamImage;
use App\Services\ImageResizeService;

class ImageController extends Controller
{
    protected $imageResizeService;

    public function __construct(ImageResizeService $imageResizeService)
    {
        $this->imageResizeService = $imageResizeService;
    }

    public function index(Request $request)
    {
        if ($request->has('site_id')) {
            $site = \App\Models\Site::findOrFail($request->site_id);
        } else {
            $site = Controller::getClientFromHost();
        }
        
        // Get all teams associated with the site
        $teams = $site->teams;
        
        // make sure the user is a member of at least one team or a super admin
        $hasAccess = false;
        foreach ($teams as $team) {
            if (\Auth::user()->isTeamMemberOrOwner($team->id)) {
                $hasAccess = true;
                break;
            }
        }
        
        if (!$hasAccess) {
            abort(403, 'Unauthorized action.');
        }

        // Get all team IDs
        $teamIds = $teams->pluck('id')->toArray();

        // Retrieve all images from all teams associated with this site
        $images = TeamImage::whereIn('team_id', $teamIds)->get();

        // Pass the images to the view along with teams for reference
        return view('image-library', [
            'images' => $images,
            'site' => $site,
            'teams' => $teams
        ]);
    }
    
    public function upload(Request $request)
    {
        try {
            // Validate site_id if provided
            if ($request->has('site_id')) {
                $validator = \Validator::make($request->all(), [
                    'site_id' => 'required|integer|exists:sites,id'
                ]);
                
                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Invalid site ID provided.'
                    ], 400);
                }
                
                $site = \App\Models\Site::findOrFail($request->site_id);
                
                // Verify user has access to this site
                $hasAccess = false;
                foreach ($site->teams as $team) {
                    if (\Auth::user()->isTeamMemberOrOwner($team->id)) {
                        $hasAccess = true;
                        break;
                    }
                }
                
                if (!$hasAccess) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Unauthorized access to this site.'
                    ], 403);
                }
            } else {
                $site = Controller::getClientFromHost();
            }
            
            // Get the current user's team for this site
            $team = null;
            foreach ($site->teams as $siteTeam) {
                if (\Auth::user()->isTeamMemberOrOwner($siteTeam->id)) {
                    $team = $siteTeam;
                    break;
                }
            }
            
            if (!$team) {
                abort(403, 'Unauthorized action. No valid team found for this site.');
            }

            // Debug logging
            \Log::info('Upload request details:', [
                'files' => $request->file('image'),
                'all' => $request->all(),
                'hasFile' => $request->hasFile('image'),
                'isValid' => $request->file('image') ? $request->file('image')->isValid() : false,
                'mimeType' => $request->file('image') ? $request->file('image')->getMimeType() : null,
                'originalName' => $request->file('image') ? $request->file('image')->getClientOriginalName() : null,
                'site_id' => $site->id,
                'team_id' => $team->id,
                'user_id' => \Auth::id()
            ]);

            // Validate the request
            $validator = \Validator::make($request->all(), [
                'image' => 'required|file|mimes:jpeg,png,jpg,gif,webp|max:8192',
                'resize' => 'required|in:true,false,0,1'
            ], [
                'image.required' => 'Please select an image to upload.',
                'image.file' => 'The uploaded file is not valid.',
                'image.mimes' => 'The image must be a file of type: JPEG, PNG, GIF, or WebP.',
                'image.max' => 'The image size must not be greater than 8MB.'
            ]);

            if ($validator->fails()) {
                \Log::error('Validation failed:', [
                    'errors' => $validator->errors()->all(),
                    'messages' => $validator->messages()->toArray(),
                    'rules' => $validator->getRules(),
                    'data' => $validator->getData()
                ]);
                return response()->json([
                    'errors' => $validator->errors()->all()
                ], 422);
            }

            // Cast resize to boolean
            $shouldResize = filter_var($request->resize, FILTER_VALIDATE_BOOLEAN);

            $file = $request->file('image');
            
            if (!$file || empty($file)) {
                return response()->json(['error' => 'No image files provided.'], 400);
            }

            try {
                // Basic validation
                if (!$file->isValid()) {
                    return response()->json(['error' => "File '{$file->getClientOriginalName()}' failed validation."], 400);
                }

                $fileSize = $file->getSize() / 1024 / 1024; // Convert to MB
                
                // Handle large files
                if ($fileSize > 8) {
                    if (!$shouldResize) {
                        return response()->json(['error' => "File '{$file->getClientOriginalName()}' is too large. Maximum allowed size is 8MB."], 400);
                    }

                    try {
                        // Attempt to resize the image
                        $resizedPath = $this->imageResizeService->resize($file);
                        if (!$resizedPath) {
                            return response()->json(['error' => "Unable to resize '{$file->getClientOriginalName()}' while maintaining acceptable quality."], 400);
                        }

                        // Create a new UploadedFile instance from the resized image
                        $file = new \Illuminate\Http\UploadedFile(
                            $resizedPath,
                            $file->getClientOriginalName(),
                            $file->getClientMimeType(),
                            null,
                            true
                        );
                    } catch (\Exception $e) {
                        \Log::error("Resize error for {$file->getClientOriginalName()}: " . $e->getMessage());
                        return response()->json(['error' => "Failed to resize '{$file->getClientOriginalName()}': " . $e->getMessage()], 500);
                    }
                }

                // Process the image upload using the current team
                $result = $this->processImageUpload($file, $team, $site);
                
                if ($result->getStatusCode() === 200) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Image uploaded successfully.',
                        'uploaded' => [$file->getClientOriginalName()]
                    ], 200);
                } else {
                    return response()->json([
                        'error' => "Failed to upload '{$file->getClientOriginalName()}'.",
                        'message' => $result->getContent()
                    ], 400);
                }
            } catch (\Exception $e) {
                \Log::error("Upload error for {$file->getClientOriginalName()}: " . $e->getMessage());
                return response()->json(['error' => "Failed to process '{$file->getClientOriginalName()}': " . $e->getMessage()], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Upload controller error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error uploading image: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function processImageUpload($file, $team, $site = null)
    {
        try {
            if ($site == null) {
                $site = Controller::getClientFromHost();
            }
            
            // Validate the file
            $this->validate(request(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120'
            ]);
    
            $filename = $file->getClientOriginalName();
            
            // If site_id was provided in request, use site's image_folder
            if (request()->has('site_id')) {
                \Log::info('Using site image folder: ' . $site->image_folder);
                $filePath = $site->image_folder . $filename;
            } else {
                // Use the default team-based folder structure
                $filePath = $site->image_folder . config('constants.USER_IMAGE_FOLDER') . $team->id . '/' . $filename;
            }

            \Log::info('Starting file upload', [
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'original_name' => $file->getClientOriginalName(),
                'temp_path' => $file->getRealPath(),
                'bucket' => config('filesystems.disks.s3.bucket'),
                'region' => config('filesystems.disks.s3.region'),
                'url' => config('filesystems.disks.s3.url'),
                'use_path_style' => config('filesystems.disks.s3.use_path_style_endpoint', false)
            ]);

            // Get file contents and verify
            $fileContents = file_get_contents($file->getRealPath());
            if ($fileContents === false) {
                throw new \Exception('Failed to read file contents');
            }

            // Log first 100 bytes for verification
            \Log::debug('File content sample (first 100 bytes): ' . substr($fileContents, 0, 100));

            try {
                // Get the S3 client instance
                $s3 = app('aws.s3');

                // Log bucket existence
                try {
                    try {
            $bucketExists = $s3->doesBucketExist(config('filesystems.disks.s3.bucket'));
        } catch (\Exception $e) {
            \Log::error('S3 bucket check error: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to verify S3 bucket. Please try again later.'], 500);
        }
                    \Log::info('S3 Bucket check', [
                        'bucket' => config('filesystems.disks.s3.bucket'),
                        'exists' => $bucketExists,
                        'region' => config('filesystems.disks.s3.region')
                    ]);

                    if (!$bucketExists) {
                        throw new \Exception('S3 bucket does not exist or is not accessible');
                    }
                } catch (\Exception $e) {
                    \Log::error('S3 Bucket check failed', [
                        'error' => $e->getMessage(),
                        'bucket' => config('filesystems.disks.s3.bucket'),
                        'region' => config('filesystems.disks.s3.region')
                    ]);
                    throw $e;
                }

                // Upload to S3 using the client directly
                $result = $s3->putObject([
                    'Bucket' => config('filesystems.disks.s3.bucket'),
                    'Key'    => $filePath,
                    'Body'   => $fileContents,
                    'ContentType' => $file->getMimeType(),
                    'ACL'    => 'public-read'
                ]);

                \Log::info('S3 Upload result', [
                    'result' => $result->toArray(),
                    'status' => $result['@metadata']['statusCode'] ?? null,
                    'effective_uri' => $result['@metadata']['effectiveUri'] ?? null,
                    'object_url' => $result['ObjectURL'] ?? null
                ]);

                // Verify file exists in S3
                try {
                    $head = $s3->headObject([
                        'Bucket' => config('filesystems.disks.s3.bucket'),
                        'Key'    => $filePath
                    ]);

                    \Log::info('S3 File verification', [
                        'exists' => true,
                        'content_length' => $head['ContentLength'] ?? null,
                        'content_type' => $head['ContentType'] ?? null,
                        'last_modified' => $head['LastModified'] ?? null
                    ]);

                    // Save the image path to the database
                    $image = new TeamImage;
                    $image->team_id = $team->id;
                    $image->path = $filePath;
                    $image->save();

                    \Log::info('Successfully saved image to database', [
                        'image_id' => $image->id,
                        'team_id' => $team->id,
                        'path' => $filePath
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Image uploaded successfully.',
                        'path' => $filePath,
                        'url' => $result['ObjectURL'] ?? null
                    ], 200);

                } catch (\Aws\S3\Exception\S3Exception $e) {
                    \Log::error('S3 File verification failed', [
                        'error' => $e->getMessage(),
                        'code' => $e->getAwsErrorCode(),
                        'request_id' => $e->getAwsRequestId(),
                        'type' => get_class($e)
                    ]);
                    throw new \Exception('Failed to verify file in S3 after upload: ' . $e->getMessage());
                }
            } catch (\Exception $e) {
                \Log::error('Image upload error: ' . $e->getMessage());
                return response()->json([
                    'error' => 'Error uploading image: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Image upload error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error uploading image: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function cleanupTempFiles()
    {
        try {
            $tempDir = sys_get_temp_dir();
            $files = glob($tempDir . '/resize_*');
            $files = array_merge($files, glob($tempDir . '/final_*'));
            
            foreach ($files as $file) {
                if (is_file($file) && (time() - filemtime($file) > 3600)) {
                    @unlink($file);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Cleanup error: ' . $e->getMessage());
        }
    }

    protected function getMaxFileSize()
    {
        $maxSize = min(
            $this->parseSize(ini_get('upload_max_filesize')),
            $this->parseSize(ini_get('post_max_size')),
            8 * 1024 * 1024 // 8MB default
        );
        return floor($maxSize / (1024 * 1024)); // Convert to MB
    }

    protected function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);
        
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }
        
        return round($size);
    }

    public function confirmResize(Request $request)
    {
        $originalFile = $request->session()->get('original_image');
        
        if (!$originalFile) {
            return redirect()->back()->with('error', 'Original image not found. Please try uploading again.');
        }

        // Attempt to resize the image
        $resizedPath = $this->imageResizeService->resize($originalFile);
        
        if (!$resizedPath) {
            return redirect()->back()
                ->with('error', 'Unable to resize image to under 5MB while maintaining acceptable quality. Please try with a different image.');
        }

        // Create a new UploadedFile instance from the resized image
        $resizedFile = new \Illuminate\Http\UploadedFile(
            $resizedPath,
            $originalFile->getClientOriginalName(),
            $originalFile->getClientMimeType(),
            null,
            true
        );

        // Clear the session
        $request->session()->forget('original_image');

        return $this->processImageUpload($resizedFile, $request->session()->get('team'));
    }
}
