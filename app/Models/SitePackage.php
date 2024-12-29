<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SitePackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function sites()
    {
        return $this->belongsToMany(Site::class, 'site_package_subscriptions', 'package_id', 'site_id')
            ->withPivot(['subscribed_at', 'expires_at', 'is_active'])
            ->withTimestamps();
    }

    public function isSubscribed(Site $site)
    {
        return $this->sites()
            ->wherePivot('site_id', $site->id)
            ->wherePivot('is_active', true)
            ->wherePivot('expires_at', '>', now())
            ->exists();
    }
}
