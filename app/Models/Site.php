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
        'description',
        'host',
        'main_color',
        'logo_image',
        'database',
        'favicon',
        'supports_registration'
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

    public function createTeam($userid){
        $team = Team::forceCreate([
            'name' => $this->site_name,
            'user_id' => $userid,
            'personal_team' => false,          
            'phone' => '',
        ]);

        //and the teamsite table needs to be updated
        TeamSite::create([$team->id, $this->id]);
        return $team;
    }

    public function addDefaultSitePages(){
        //two templates, welcome.txt and dashboard.txt
        $content = file_get_contents(resource_path() . '/templates/welcome.txt');
        $content = str_replace('SITE_NAME', $this->site_name, $content);
        $content = str_replace('SITE_LOGO_FILE', $this->logo_image, $content);
        $content = str_replace('SITE_FAVICON_FILE', $this->favicon, $content);
        $content = str_replace('SITE_DESCRIPTION', $this->description, $content);
        $welcomepage = SitePages::firstOrCreate(['fk_site_id'=>$this->id,'section'=>'Welcome'],
            ['description'=>$content,  'title'=>'Welcome','url'=>'html']);
        $welcomepage->save();

        //dashboard page
        $content = file_get_contents(resource_path() . '/templates/dashboard.txt');
        $dashboardpage = SitePages::firstOrCreate(['fk_site_id'=>$this->id,'section'=>'Dashboard'],
            ['description'=>$content,  'title'=>'Dashboard','url'=>'html']);
        $dashboardpage->save();

       
    }
}
