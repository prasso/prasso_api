<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;

class LivestreamSettings extends Model
{
    use HasFactory;    
    use HasTimestamps;
    

    protected $table = 'livestream_settings';
    protected $fillable = [
        'fk_site_id' ,'queue_folder', 'presentation_folder'
    ];

    protected $hidden = ['created_at','updated_at'];
    
    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class,'fk_site_id','id');
    } 

    public static function remove( $site_id){
        $livestream_settings = LivestreamSettings::where('fk_site_id',$site_id)->first();
        if ($livestream_settings != null){
            $livestream_settings->delete();
        }
    }
    
    public static function addOrUpdate($site){
        $host = str_replace('.faxt.com','',$site->host).'/';
        $queue_folder = 'faith-baptist-livestream/ivs/v1/629811581977/CG4sjCYs40kT/';//this is from the livestream dashboard at AWS
        $presentation_folder = $host.'/hls/';
        
        LivestreamSettings::updateOrCreate(
            ['fk_site_id' => $site->id],
            ['queue_folder' => $queue_folder, 'presentation_folder' => $presentation_folder]
        );
    }
}
