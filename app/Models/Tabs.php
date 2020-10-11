<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tabs extends Model
{
    use HasFactory;
    protected $fillable = [
        ' app_id', 'icon', 'label', 'page_title', 'page_url', 'sort_order',
    ];

}