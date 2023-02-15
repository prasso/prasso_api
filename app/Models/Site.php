<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Auth;
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
        'supports_registration',
        'app_specific_js',
        'app_specific_css'
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
        $content = '';
        if ($this->supports_registration)
        {
            $content = file_get_contents(resource_path() . '/templates/welcome.txt');
        }
        else{
            $content = file_get_contents(resource_path() . '/templates/welcome_no_register.txt');
        }
        $welcomepage = SitePages::firstOrCreate(['fk_site_id'=>$this->id,'section'=>'Welcome'],
            ['description'=>$content,  'title'=>'Welcome','url'=>'html']);
        $welcomepage->save();

        //if this site supports registration, then create a dashboard page
        if ($this->supports_registration)
        {
            $content = file_get_contents(resource_path() . '/templates/dashboard.txt');
            $dashboardpage = SitePages::firstOrCreate(['fk_site_id'=>$this->id,'section'=>'Dashboard'],
                ['description'=>$content,  'title'=>'Dashboard','url'=>'html']);
            $dashboardpage->save();
        }
    }
    // a function to get the site pages for this site
    public function getSitePages()
    {
        $sitepages = SitePages::where('fk_site_id',$this->id)->get();
        return $sitepages;
    }

    public function getSiteMapList()
    {
        $sitepages = $this->getSitePages();
        $sitemap = array();
        foreach ($sitepages as $page)
        {
            $sitemap[$page->section] = $page->title;
        }
        //format $sitemap into a list of LIs
        $list = '';
        foreach ($sitemap as $key => $value)
        {
            $list .= '<li><a href="/page/' . $key . '">' . $value . '</a></li>';
        }
        
        // if this user is an admin then add the site editor
        if (Auth::user() !=null && Auth::user()->isInstructor())
        {
            $list .= '<li><a href="/site/edit">Site Editor</a></li>';
        }

    
        return $list;
    }

    public function getDarkFontColorFromMainColor(){
        // write code here that looks at the site main color and returns a border color which is a shade darker
        // first create a color from the main color
        $color = $this->adjustBrightness($this->main_color,-10);
        return $color;


    }
    
    public function getNavBackgroundFromMainColor(){
        // write code here that looks at the site main color and returns a border color which is a shade darker
        // first create a color from the main color
        $color = $this->adjustBrightness($this->main_color,100);
        return $color;


    }
    
    public function getBorderColorFromMainColor(){
        // write code here that looks at the site main color and returns a border color which is a shade darker
        // first create a color from the main color
        $color = $this->adjustBrightness($this->main_color,-1);
        return $color;


    }
    
    function adjustBrightness($hex, $steps) {
        // Steps should be between -255 and 255. Negative = darker, positive = lighter
        $steps = max(-255, min(255, $steps));
    
        // Normalize into a six character long hex string
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
        }
    
        // Split into three parts: R, G and B
        $color_parts = str_split($hex, 2);
        $return = '#';
    
        foreach ($color_parts as $color) {
            $color   = hexdec($color); // Convert to decimal
            $color   = max(0,min(255,$color + $steps)); // Adjust color
            $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
        }
    
        return $return;
    }
}
