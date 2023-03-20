<?php
namespace App\Services;


use App\Models\LivestreamSettings;
use App\Models\Instructor;
use App\Models\Site;
use App\Models\SiteMedia;
use Illuminate\Support\Facades\Storage;

class MediaService 
{


    public function __construct()
    {
        
    }

    public function getSiteFromRequest($request){

      $site = Site::where('id' ,  $request['siteid'] )
      ->with(['media' => function ($query)  {
        $query->orderBy('media_date', 'desc');
        }])
      ->with('livestream_settings')->first();
      return $site;
    }

    private function mediaIsOkToEdit($site){
     if ($site->livestream_settings == null)
      {
        session()->flash('message', 'No livestream settings found for this site. Please setup your livestream. ');
        return false;
      }

      if (!Instructor::canAccessSite(\Auth::user(),$site))
      {
        session()->flash('message', 'You do not have access to this site. ');
        return false;
      }
      return true;
    }

    public function getSiteMediaDetailsFromRequest($request){
        //look this up at s3 for details
        $site = $this->getSiteFromRequest($request);
        if (!$this->mediaIsOkToEdit($site)){
            return null;
        }
        $livestream_settings = $site->livestream_settings;
        $edit_date=SiteMedia::extractMediaDateFromRequest($request);
        $folder_to_be_moved = $this->buildFolderFromDate($edit_date.'/', $livestream_settings->queue_folder);

        $folder_to_receive = $livestream_settings->presentation_folder.$edit_date.'/';
        $index_and_thumb_array = $this->moveMediaFilesToFolder($folder_to_be_moved, $folder_to_receive, $livestream_settings);
        
        $newid = SiteMedia::create([
            'fk_site_id' => $site->id,
            's3media_url' => $new_filepath = str_replace('media.prasso.io/hls/','media.prasso.io/',config('constants.CLOUDFRONT_MEDIA_URL') . $index_and_thumb_array['index']),
            'thumb_url' => $new_filepath = str_replace('media.prasso.io/hls/','media.prasso.io/',config('constants.CLOUDFRONT_MEDIA_URL') .$index_and_thumb_array['thumb']),
            'media_title' => 'Livestream on '.$edit_date,
            'media_description' => $site->site_name.' Livestream on '.$edit_date,
            'media_date' => $edit_date,

        ]);
        $site_media = SiteMedia::where('fk_site_id', $site->id)->where('id', $newid)->first();
        return $site_media;
    }
    
    private function buildFolderFromDate($edit_date, $baseLocation){
        $folder_to_be_moved = $edit_date;
        $folder_to_be_moved = str_replace('-','/',$folder_to_be_moved);
        $folder_to_be_moved = str_replace(' ','/',$folder_to_be_moved);
        $folder_to_be_moved = str_replace(':','/',$folder_to_be_moved);
        $folder_to_be_moved = $baseLocation . $folder_to_be_moved;
        return $folder_to_be_moved;
    }

    private function moveMediaFilesToFolder($folder_to_be_moved, $folder_to_receive, $livestream_settings){
        $bucket = $this->getBucket($livestream_settings->queue_folder);
        $storage = $this->createDrive($bucket);
        
        $file_path = str_replace($bucket.'/','',$folder_to_be_moved);
        
        $index_and_thumb_array = $this->moveFiles($storage, $file_path, $folder_to_receive);
        return $index_and_thumb_array;
    }

    private function moveFiles($storage, $folder_to_be_moved, $folder_to_receive){
        $type = '/media/';
        $index_and_thumb_array = ['index'=>'','thumb'=>''];
        $files = $storage->AllFiles($folder_to_be_moved);

        foreach ($files as $file) {
           usleep(300);
           $new_file = config('constants.CLOUDFRONT_MEDIA_BASE_PATH').$folder_to_receive . substr($file, strpos($file, $type) + strlen($type));

            if (!strpos($file, $type) > 0) {
              $storage->delete($file);
              continue;
            }
            if ($storage->exists($new_file)){
                $storage->delete($new_file);
            }
            $storage->move($file, $new_file);
            if (strpos($file, 'master.m3u8') > 0) {
                $index_and_thumb_array['index'] = $new_file;
            }
            if (strpos($file, 'thumb') > 0 &&  $index_and_thumb_array['thumb'] == '' && strpos($file, 'thumb20') > 0) {
                $index_and_thumb_array['thumb'] = $new_file;
            }
        }
      return $index_and_thumb_array;
    }

    public function getUnQueuedLivestreams($site){
      if (!$this->mediaIsOkToEdit($site)){
        return [];
      }
      
      $livestream_settings = $site->livestream_settings;
      $bucket = $this->getBucket($livestream_settings->queue_folder);
      $file_path = str_replace($bucket,'',$livestream_settings->queue_folder);

      $storage = $this->createDrive($bucket);
      $livestreams = $storage->AllFiles('ivs');
      $livestreams = array_map(function($item) use ($file_path) {
        $otheritem = "/".str_replace("\/","/",$item);
        return str_replace($file_path,'', $otheritem);
      }, $livestreams);
      
      $livestreams = array_unique(array_map(function($item) {
        $parts = explode('/',$item);
        return $parts[0] . '/' . $parts[1] . '/' . $parts[2];
      }, $livestreams));

      return $livestreams;
  }

    private function getBucket($folder){
        $parts = explode('/',$folder);
        return $parts[0];
    }

    private function createDrive($bucket){
        $storage = \Storage::createS3Driver([
            'driver'     => 's3',
            'bucket'       => $bucket,
            'region' => 'us-east-1',
        ]);
        return $storage;
    }

}