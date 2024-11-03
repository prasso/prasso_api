<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stripe extends Model
{
    
    use HasFactory;

    protected $table = 'stripe';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'site_id',
        'key',
        'secret', // If you need it
    ];

    /**
     * Get the site that owns the Stripe configuration.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
