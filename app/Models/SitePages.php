<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SitePages extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var array
    */
    protected $fillable = [
        'fk_site_id', 'section', 'title', 'description', 'url','headers','masterpage','template','style','login_required','user_level','where_value','page_notifications_on'
    ];
    
    public function site()
    {
        return $this->belongsTo(Site::class, 'fk_site_id');
    }

    public function site_page_data(){
        return $this->hasMany(\App\Models\SitePageData::class, "fk_site_page_id", "id");
    }

    public function requiresAuthentication()
    {
        return $this->login_required;
    }

    public static function pageNotificationsRequested($pageid){
        $page = SitePages::find($pageid)->firstOr(function(){return null;});

        return $page->page_notifications_on;
    }
    public function pageRequiresAdmin(){
        return $this->user_level ;
    }
}
