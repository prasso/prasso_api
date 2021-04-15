<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
/**
 * Class Site.
 *
 * @property int $id
 * @property string site_name
 * @property string $host
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 */
class Site extends Model
{
    use HasFactory;

    protected $currentsite;

    protected $table = 'sites';
    public $timestamps = true;

    protected $fillable = [
        'id',
        'site_name',
        'host',
        'main_color',
        'logo_image',
        'database',
        'favicon'
    ];

    public static function getClient( $host) 
    {
        $host = $host;

        $currentsite =  self::where('host' ,  $host )
                ->orWhere('host', 'like', '%' . $host . '%')->get()->first();

        if ($currentsite != null)
        {
            $id = $currentsite->id;
        }
        return $currentsite;
    }
}
