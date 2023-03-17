<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteMedia extends Model
{
    use HasFactory;

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class,'fk_site_id','id');
    }
}
