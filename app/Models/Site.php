<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
/**
 * Class Site.
 *
 * @property int $id
 * @property string $host
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 */
class Site extends Model
{
    use HasFactory;

    protected $currentsite;

    protected $fillable = [
        'id',
        'host'
    ];
    
    public static function getClient( $host) 
    {
        $host = $host;
        $currentsite =  self::where('host' , $host )->get()->first();
        if ($currentsite != null)
        {
            $id = $currentsite->id;
        }
        return $currentsite;
    }
}
