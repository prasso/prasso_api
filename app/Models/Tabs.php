<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;

/*
    Apps have Tabs
*/
class Tabs extends Model
{
    use HasTimestamps;
    
    use HasFactory;
    protected $fillable = [
        'app_id', 'icon', 'label', 'page_title', 'page_url', 'sort_order','parent'
    ];


    
    public function app()
    {
        return $this->belongsTo(Apps::class);
    }
}