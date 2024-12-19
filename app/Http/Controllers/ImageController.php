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
        $file = $request->file('image');
        
        if (!$file) {
            return response()->json(['error' => 'No image file provided.'], 400);
        }

        // Get the file size in MB
        $fileSize = $file->getSize() / 1024 / 1024;
        
        // Check if file is too large (use server's upload_max_filesize from php.ini)
        if ($fileSize > ini_get('upload_max_filesize')) {
            return response()->json([
                'error' => 'The file is too large. Maximum allowed size is ' . ini_get('upload_max_filesize') . '.'
            ], 400);
        }

        // Check if image needs resizing
        if ($this->imageResizeService->needsResize($file)) {
            // Store original file in session for potential resizing
            $request->session()->put('original_image', $file);
            
            return response()->json([
                'warning' => 'Image size exceeds 5MB. Would you like to automatically resize it?',
                'show_resize_options' => true,
                'file_size' => round($fileSize, 2) . 'MB'
            ], 200);
        }

        return $this->processImageUpload($file);
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
