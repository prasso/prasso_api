<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Faxt\Invenbin\Models\ErpProduct;
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

    const HOSTING_SITE_ID = 1;

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
        'invitation_only',
        'image_folder',
        'github_repository',
        'deployment_path',
        'pwa_enabled',
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

    // Site.php Model
    public function stripe()
    {
        return $this->hasOne(Stripe::class); // Or whatever the actual relationship is
    }
    /**
     * Get the Stripe key for the site. If no related stripe record exists, return the default key from config.
     *
     * @return string
     */
    public function getStripeKeyAttribute()
    {
        // If the site has a related Stripe record, return the key, otherwise return the default key from config
        return $this->stripe ? $this->stripe->key : config('services.stripe.key');
    }

    /**
     * The products that are associated with the site.
     */
    public function erpProducts()
    {
        return $this->belongsToMany(ErpProduct::class, 'site_erp_products', 'site_id', 'erp_product_id')
                    ->withTimestamps();
    }

    public function getApp()
    {
        return optional($this->app)->id;
    }

    public function packages()
    {
        return $this->belongsToMany(SitePackage::class, 'site_package_subscriptions', 'site_id', 'package_id')
            ->withPivot(['subscribed_at', 'expires_at', 'is_active'])
            ->withTimestamps();
    }

    public function hasPackage($packageSlug)
    {
        return $this->packages()
            ->where('slug', $packageSlug)
            ->wherePivot('is_active', true)
            ->wherePivot('expires_at', '>', now())
            ->exists();
    }

    public static function isPrasso($host) 
    {
        $host = request()->getHttpHost();     
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
        // Check if this is a load balancer health check request
        // AWS ELB health checks typically use the ELB DNS name or IP address
        if (static::isLoadBalancerHealthCheck($host)) {
            // Silently return null for load balancer health checks
            // This prevents unnecessary log entries from AWS health checks
            return null;
        }
        
        // Use exact matching for better performance
        $currentsite = null;
        
        // Build query to find sites containing this exact host
        if (Schema::hasTable('livestream_settings')) {
            $currentsite = Site::where('host', 'like', '%,' . $host . ',%')
                ->orWhere('host', 'like', $host . ',%')
                ->orWhere('host', 'like', '%,' . $host)
                ->orWhere('host', $host)
                ->with('livestream_settings')
                ->get();
        } else {
            $currentsite = Site::where('host', 'like', '%,' . $host . ',%')
                ->orWhere('host', 'like', $host . ',%')
                ->orWhere('host', 'like', '%,' . $host)
                ->orWhere('host', $host)
                ->get();
        }
        
        // Verify exact match in comma-separated list
        if ($currentsite && count($currentsite) > 0) {
            foreach ($currentsite as $site) {
                $hosts = array_map('trim', explode(',', $site->host));
                if (in_array($host, $hosts)) {
                    return $site;
                }
            }
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

    /**
     * Detect if the request is from an AWS load balancer health check.
     * AWS ELB health checks use the ELB DNS name or IP address, which won't match
     * any configured site hosts. This method identifies such requests to prevent
     * unnecessary error logging.
     *
     * @param string $host The host header from the request
     * @return bool True if this appears to be a load balancer health check
     */
    private static function isLoadBalancerHealthCheck($host)
    {
        // Check if host matches AWS ELB DNS pattern (e.g., faxt-prod-lb-1670714123.us-east-1.elb.amazonaws.com)
        if (preg_match('/\.elb\.amazonaws\.com$/', $host)) {
            return true;
        }
        
        // Check if host is an IP address (load balancers often use IP addresses for health checks)
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return true;
        }
        
        return false;
    }

    public function assignToUserTeam($current_user_id){
        //team is the first team of the user that isn't prasso
        $team = Team::where('user_id', $current_user_id)->where('id', '!=', 1)->first();
        if ($team == null)
        {
            $team = $this->createTeam($current_user_id);
        }
        
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
        
        //and the teamsite table needs to be updated, if not prasso
        if ($this->id != 1)
        {
            $teamsite = TeamSite::where('team_id' , $team->id)->where( 'site_id' , $this->id)->first();
            if (!isset($teamsite)){TeamSite::create(['team_id' => $team->id, 'site_id' => $this->id]);}
        }
        return $team;
    }

    public function addDefaultSitePages(){
        //uses templates, welcome.txt, dashboard.txt
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
            ,'template'=>'sitepage.templates.blankpage','style'=>'','where_value'=>'','page_notifications_on'=>false]);
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
        $is_admin_for_site =  Auth::user() !=null && ( Auth::user()->isInstructor($this)  );
        return $is_admin_for_site;
    }
    
    public function superAdmin()
    {
        // Find the first super-admin user in the team that owns this site
        return User::whereHas('roles', function ($query) {
                $query->where('role_name', config('constants.SUPER_ADMIN_ROLE_TEXT'));
            })
            ->first();
    }
    
    // Helper function to get the hosting site instance
    public static function hostingSite()
    {
        return static::find(self::HOSTING_SITE_ID);
    }

    

    /*
    Use this function when the team needs to be traded out, old for new. 
    And the user must have specific admin abilities.
     */
    public function updateTeam($teamId)
    {
        $currentTeam = null;
        $currentTeam = $this->teams()->firstOr(function () {
                return null;
            });

        $user = Auth::user();
        if ($user->isSuperAdmin() && $teamId != null) {
        
            if ($currentTeam) {
                $this->teams()->detach($currentTeam);
            }
            $currentTeam = Team::find($teamId);
            $this->teams()->attach($currentTeam);
            
        }
        else {
            if ($this->teams()->count() == 0) {
                $currentTeam = $this->assignToUserTeam($user->id);
            }
        }
        return $currentTeam;
    }

    public function getSiteMapList($current_page = null)
    {
        // Get all visible top-level menu items
        $topLevelPages = $this->sitePages()->topLevel()->visible()->get();
        $list = '';
        
        foreach ($topLevelPages as $page)
        {
            $lcase_section = strtolower($page->section);
            $lcase_page = strtolower($current_page);
            
            // Skip if this is the current page
            if ($page != null && ('/page/'.$lcase_section == $lcase_page || $lcase_section == $lcase_page)) {
                continue;
            }
            
            // Check user access level
            if ($page->user_level == false || $this->user_is_admin()) {
                // Add top level menu item
                $list .= '<li><a class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 transition duration-150 ease-in-out" href="/page/' . $page->section . '">' . $page->title . '</a>';
                
                // Get submenu items
                $subMenuItems = $page->subMenuItems()->visible()->get();
                if ($subMenuItems->count() > 0) {
                    $list .= '<ul class="ml-4">';
                    foreach ($subMenuItems as $subItem) {
                        if ($subItem->user_level == false || $this->user_is_admin()) {
                            $list .= '<li><a class="block pl-3 pr-4 py-2 text-base font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition duration-150 ease-in-out" href="/page/' . $subItem->section . '">' . $subItem->title . '</a></li>';
                        }
                    }
                    $list .= '</ul>';
                }
                $list .= '</li>';
            }
        }
        
        // Add admin links if user is admin
        if ($this->user_is_admin()) {
            $list .= '<li><a class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 transition duration-150 ease-in-out" href="/site/edit">Site Editor</a></li>';
            $this->load('app');
            if ($this->app) {
                $this->app->load('team');
                if ($this->app->team) {
                    $list .= '<li><a class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 transition duration-150 ease-in-out" href="/team/' . $this->app->team->id . '/apps/' . $this->app->id . '">App Editor</a></li>';
                }
            }
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
        // Get the original filename
        $originalName = $photo->getClientOriginalName();
        
        // Store with original filename
        $photo->storeAs(
            config('constants.APP_LOGO_PATH') .'logos-'.$this->id, 
            $originalName, 
            's3'
        );
        
        // Return the URL with the original filename
        $logo_image = config('constants.CLOUDFRONT_ASSET_URL') . config('constants.APP_LOGO_PATH') .'logos-'.$this->id.'/'. $originalName;
        return $logo_image;
    }

    public function teamFromSite(){
       //get the first team from the site, it was added when the site was created
        $teamSite = $this->teams()->first();
        if ($teamSite == null) {
            Log::error('TeamSite not found for site: ' . $this->id);
            $teamSite = TeamSite::where('site_id', 1)->first();
        }
        return optional($teamSite);
    }

    /**
     * returns the user who owns the first team of this site
     */
    public function getTeamOwner($site){
        $ownerTeam = $this->teamFromSite();
        $owner = User::find($ownerTeam->user_id)->firstOr(function(){return null;});
        return $owner;
    }
}