<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\TeamImage;
use App\Services\ImageResizeService;

class ImageController extends BaseController
{
    protected $imageResizeService;

    public function __construct(ImageResizeService $imageResizeService)
    {
        $this->imageResizeService = $imageResizeService;
    }

    public function index()
    {
       $site = Controller::getClientFromHost();
       $team = $site->teams()->first();  // Get the team associated with the site
        
       // make sure the user is a member of the team or a super admin
        if ( !\Auth::user()->isTeamMemberOrOwner($team->id)) {
            abort(403, 'Unauthorized action.');
        }

        // Retrieve the images from S3
        $images = TeamImage::where('team_id', $team->id)->get();

        // Pass the images to the view
        return view('image-library', ['images' => $images, 'site' => $site, 'team' => $team]);
    }
    
    public function upload(Request $request)
    {
        $site = Controller::getClientFromHost();
        $team = $site->teams()->first();  // Get the team associated with the site
         
        // make sure the user is a member of the team or a super admin
        if ( !\Auth::user()->isTeamMemberOrOwner($team->id)) {
            abort(403, 'Unauthorized action.');
        }

        $files = $request->file('image');
        $shouldResize = $request->boolean('resize');
        
        if (!$files || empty($files)) {
            return response()->json(['error' => 'No image files provided.'], 400);
        }

        $results = [];
        $errors = [];
        $maxFileSize = $this->getMaxFileSize();

        foreach ($files as $file) {
            try {
                // Validate file type and size
                if (!$file->isValid() || !str_starts_with($file->getMimeType(), 'image/')) {
                    $errors[] = "File '{$file->getClientOriginalName()}' is not a valid image.";
                    continue;
                }

                $fileSize = $file->getSize() / 1024 / 1024; // Convert to MB
                
                if ($fileSize > $maxFileSize) {
                    if (!$shouldResize) {
                        $errors[] = "File '{$file->getClientOriginalName()}' is too large. Maximum allowed size is {$maxFileSize}MB.";
                        continue;
                    }

                    // Resize the image
                    $resizedPath = $this->imageResizeService->resize($file);
                    if (!$resizedPath) {
                        $errors[] = "Unable to resize '{$file->getClientOriginalName()}' while maintaining acceptable quality.";
                        continue;
                    }

                    // Create a new UploadedFile instance from the resized image
                    $file = new \Illuminate\Http\UploadedFile(
                        $resizedPath,
                        $file->getClientOriginalName(),
                        $file->getClientMimeType(),
                        null,
                        true
                    );
                }

                // Process the image upload
                $result = $this->processImageUpload($file);
                if ($result->getStatusCode() === 200) {
                    $results[] = $file->getClientOriginalName();
                } else {
                    $errors[] = "Failed to upload '{$file->getClientOriginalName()}'.";
                }
            } catch (\Exception $e) {
                \Log::error('Image upload error: ' . $e->getMessage());
                $errors[] = "Error processing '{$file->getClientOriginalName()}': {$e->getMessage()}";
            }
        }

        // Prepare the response
        $response = [
            'uploaded' => count($results),
            'failed' => count($errors),
            'message' => count($results) . ' image(s) uploaded successfully'
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        $statusCode = empty($results) ? 400 : 200;
        return response()->json($response, $statusCode);
    }

    protected function getMaxFileSize()
    {
        $maxSize = ini_get('upload_max_filesize');
        if (str_ends_with($maxSize, 'M')) {
            return (int)$maxSize;
        }
        return 8; // Default to 8MB if we can't determine the server setting
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

        return $this->processImageUpload($resizedFile);
    }

    protected function processImageUpload($file)
    {

        try {
            $site = Controller::getClientFromHost();
            $team = $site->teams()->first();  // Get the team associated with the site
            
            // Validate the file
            $this->validate(request(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120'
            ]);
    
            $filename = $file->getClientOriginalName();
            $filePath = $site->image_folder . config('constants.USER_IMAGE_FOLDER').$team->id.'/'.$filename;
            \Storage::disk('s3')->put($filePath, file_get_contents($file));
    
            // Save the image path to the database
            $image = new TeamImage;
            $image->team_id = $team->id;
            $image->path = $filePath;
            $image->save();
    
            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully.'
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Image upload error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error uploading image: ' . $e->getMessage()
            ], 500);
        }
    }
}
