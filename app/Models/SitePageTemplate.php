<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SitePageTemplate extends Model
{
    use HasFactory;
    protected $fillable = [
        'templatename',
        'title',
        'description',
        'template_data_model',
        'template_where_clause',
        'template_data_query',
        'order_by_clause',
        'default_blank',
        'include_csrf',
        
    ];

    public static function getDefaultBlank()
    {
        return new static([
            'templatename' => 'sitepage.templates.xxx',
            'title' => '',
            'description' => '',
            'template_data_model' => 'App\Models\SiteMedia',
            'template_where_clause' => 'fk_site_id',
            'template_data_query' => 'media_title',
            'order_by_clause' => 'media_title',
        ]);
    }
}
