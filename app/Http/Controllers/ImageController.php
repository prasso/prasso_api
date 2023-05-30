<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\TeamImage;

class ImageController extends BaseController
{
    
public function upload(Request $request)
{
    $team = \Auth::user()->currentTeam; // Get the current user's team
    $site = Controller::getClientFromHost();
    $file = $request->file('image'); // Get the uploaded file
    $filename = $file->getClientOriginalName(); // Get the original filename
    $filePath = $site->image_folder . $filename;
    \Storage::disk('s3')->put($filePath, file_get_contents($file));      

    // Save the image path to the database
    $image = new TeamImage;
    $image->team_id = $team->id;
    $image->path = $filePath;
    $image->save();
    session()->flash('message','Image uploaded successfully.' );
    return redirect()->back()->with('success', 'Image uploaded successfully.');
}
}
