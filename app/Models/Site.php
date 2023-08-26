<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Auth;
use Schema;
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
        'site_name',
        'description',
        'host',
        'main_color',
        'logo_image',
        'database',
        'favicon',
        'supports_registration',
        'subteams_enabled',
        'app_specific_js',
        'app_specific_css',
        'does_livestreaming',
        'image_folder',
    ];

    public function livestream_settings()
    {
        return $this->hasOne(\App\Models\LivestreamSettings::class, "fk_site_id", "id");
    }
    public function media(){
        return $this->hasMany(\App\Models\SiteMedia::class, "fk_site_id", "id");
    }

    public function teams(){
        return $this->belongsToMany(Team::class, 'team_site', 'site_id', 'team_id');
    }

    public function app()
    {
        return $this->hasOne(Apps::class);
    }
 
    public function sitePages()
    {
        return $this->hasMany(\App\Models\SitePages::class, "fk_site_id", "id");
    }

    public function getApp()
    {
        return optional($this->app)->id;
    }

    public static function isPrasso($host) 
    {
        $site = Site::getClient($host);
        if ($site == null)
        {
            Log::info('Site get client failed for host: ' . $host);
            abort(404);
            return null;
        }
        return $site->id == 1? 'true' : 'false';
    }
    
    public static function getClient( $host) 
    {
        if ($host == null)
        {
            Log::info('Site get client failed for null host');
            abort(404);
            return null;
        }
    try{
        $currentsite = null;
        if (Schema::hasTable('livestream_settings')) {
           
            $currentsite = Site::where('host' ,  $host )
                ->orWhere('host', 'like', '%' . $host . '%')
                ->with('livestream_settings')
                ->get();
        }
        else
        {
            $currentsite = Site::where('host' ,  $host )
                ->orWhere('host', 'like', '%' . $host . '%')
                ->get();

        }
        if ($currentsite == null)
        {
            Log::info('Site get client failed for host: ');
            return null;
        }
        
        return $currentsite[0];
    }
    catch(\Exception $e){
        Log::info('Site get client failed for host: ' . $host . ' with error: ' . $e->getMessage());
        return null;
    }
    return null;
    }

    public function assignToUserTeam($current_user_id){
        //team is the first team of the user that isn't prasso
        $team = Team::where('user_id', $current_user_id)->where('id', '!=', 1)->first();
        if ($team == null)
        {
            $team = $this->createTeam($current_user_id);
        }
        Auth::user()->setCurrentTeam();

         //and the teamsite table needs to be updated
         TeamSite::create(['team_id' => $team->id, 'site_id' => $this->id]);
         $team->refresh();
         return $team;
    }

    public function createTeam($userid){
        if ($this->id == null || $userid == null)
        {
            throw Exception('Site or user id is null in Site::createTeam');
        }
        $team = Team::forceCreate([
            'name' => $this->site_name,
            'user_id' => $userid,
            'personal_team' => false,          
            'phone' => '',
        ]);
        if ($team->id == null)
        {
            throw Exception('Team id is null in Site::createTeam');
        }
        
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
            ['description'=>$content,  'title'=>'Welcome','url'=>'html','login_required'=>false,'user_level'=>false,'headers'=>'','masterpage'=>'sitepage.templates.blankpage'
            ,'template'=>'sitepage.templates.blankpage','style'=>'','where_value'=>'']);
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

    private function user_is_admin(){
        $is_admin_for_site =  Auth::user() !=null && ( Auth::user()->isInstructor() || Auth::user()->isThisSiteTeamOwner($this->id) );
        return $is_admin_for_site;
    }

    public function getSiteMapList($current_page = null)
    {
        $sitepages = $this->sitePages();
        $sitemap = array();
        foreach ($sitepages as $page)
        {
            $lcase_section = strtolower($page->section);
            $lcase_page = strtolower($current_page);
            
            //dont put menu items out that are the page this user is on
            if ($page != null && ('/page/'.$lcase_section == $lcase_page || $lcase_section == $lcase_page))
            {
                continue;
            }
            //dont put menu items out that this user can not access
            if ( $page->user_level == false || $this->user_is_admin() )
            {
                $sitemap[$page->section] = $page->title;
            }
            
        }
        //format $sitemap into a list of LIs
        $list = '';
        foreach ($sitemap as $key => $value)
        {
            //
            $list .= '<li><a class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out" href="/page/' . $key . '">' . $value . '</a></li>';
        }
        
        // if this user is an admin or a team owner then add the site editor
        if ( $this->user_is_admin() )
        {
            $list .= '<li><a class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out" href="/site/edit">Site Editor</a></li>';
        }

    
        return $list;
    }

    public function getDarkFontColorFromMainColor(){
        // write code here that looks at the site main color and returns a border color which is a shade darker
        // first create a color from the main color
        $color = $this->adjustBrightness($this->main_color,-10);
        return $color;
    }
    
    /**this was not pretty so I'm setting to just light gray for now */
    public function getNavBackgroundFromMainColor(){
        // write code here that looks at the site main color and returns a border color which is a shade darker
        // first create a color from the main color
        $color = "#f1f1f1"; //$this->adjustBrightness($this->main_color,100);
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

    /**
     * Uploads an image for the site
     * @param photo
     * @return string
     */
    public function uploadImage($photo){
        $photo->store(config('constants.APP_LOGO_PATH') .'logos-'.$this->id, 's3');
        $logo_image = config('constants.CLOUDFRONT_ASSET_URL') . config('constants.APP_LOGO_PATH') .'logos-'.$this->id.'/'. $photo->hashName();
        return $logo_image;
    }

    public function teamFromSite(){
       //get the first team from the site, it was added when the site was created
        $teamSite = $this->teams()->first();
        if ($teamSite == null) {
            Log::error('TeamSite not found for site: ' . $this->id);
            $teamSite = TeamSite::where('site_id', 1)->first();
        }
        return optional($teamSite)->team;
    }
}

