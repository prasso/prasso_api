<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;

class SitePageData extends Model
{
    use HasFactory; 
    use HasTimestamps;
    

    protected $table = "site_page_data";

     /**
     * The attributes that are mass assignable.
     *
     * @var array
    */
    protected $fillable = [
        'fk_site_page_id', 'data_key', 'json_data'
    ];

     public function site()
    {
        return $this->belongsTo(\App\Models\SitePages::class,'fk_site_page_id','id');
    }

}
