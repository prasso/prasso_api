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
        'fk_site_id', 'section', 'title', 'description', 'url','headers','masterpage','template','style','login_required'
    ];

    public function requiresAuthentication()
    {
        return $this->login_required;
    }
}
