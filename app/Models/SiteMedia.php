<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SiteMedia extends Model
{
    use HasFactory;

    protected $table = 'site_media';
    public static $prefix = 'media_to_queue';

    protected $fillable = [
        'fk_site_id' , 's3media_url', 'media_title','media_date', 'media_description', 'thumb_url',
         'video_duration', 'dimensions'.'media_title','media_description',
            'media_date'
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class,'fk_site_id','id');
    }

    public static function extractMediaDateFromRequest(Request $request){
        $media_date = '';
        foreach ($request->all() as $key => $value) {
            if (strpos($key, SiteMedia::$prefix) === 0) {
                $media_date=$value;
            }
        }
        return $media_date;

    }
}
