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


    public static function processUpdates( $tab_data)
    {
        $tabdata = Tabs::updateOrCreate(['id' => $tab_data['id']] , 
        ['app_id' => $tab_data['app_id'], 
        'icon' => $tab_data['icon'], 
        'label' => $tab_data['label'], 
        'page_title' => $tab_data['page_title'],
        'page_url' => $tab_data['page_url'],
        'sort_order' => $tab_data['sort_order'] ,
        'parent' => $tab_data['parent'] ] ,
        );
        return $tabdata;
    }
}