<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/*
    Apps have Tabs
*/
class Tabs extends Model
{
    use HasTimestamps;
    use HasFactory;

    protected $fillable = [
        'id' ,
        'app_id',
        'icon',
        'label',
        'page_url' , 
        'page_title' , 
        'sort_order' ,
        'parent' 
    ];

    public function app()
    {
        return $this->belongsTo(Apps::class);
    }

    public function __construct(){
        $this->id =config('constants.TAB_DEFAULT_ID');
        $this->app_id =config('constants.TAB_DEFAULT_APP_ID');
        $this->sort_order = config('constants.TAB_DEFAULT_SORT_ORDER');
        $this->parent = config('constants.TAB_DEFAULT_PARENT');
        $this->icon = config('constants.TAB_DEFAULT_ICON');
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