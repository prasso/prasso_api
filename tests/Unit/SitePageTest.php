<?php
use Tests\TestCase;
use App\Http\Controllers\SitePageController;
use Illuminate\Http\Request;
use App\Services\SitePageService;
use App\Services\UserService;
use App\Models\User;
use App\Models\SitePages;

class SitePageTest extends TestCase
{
    public function testTemplateData(){
        
        $site_page = SitePages::factory()->create();
        $site_page->description = '<div class="flex mt-10 w-full overflow-scroll pl-10 pr-10"  x-data=\'{"videos":[DATA] }\' </div>';
        $site_page->template = 'sitepage.templates.past_livestreams';
        $site_page->where_value = 48;
        $placeholder = '[DATA]'; 
        
        $user = User::factory()->create();
        $sitePageService = new SitePageService();
        $jsonData = $sitePageService->getTemplateDataJSON($site_page, $user);
    
        $site_page_description = str_replace($placeholder, $jsonData, $site_page->description);
    info('site page description: '.$site_page_description);
        $this->assertTrue(strpos($site_page_description, 'x-data=\'{"videos": }') == false);
           
        }
    
      /**
     * @runInSeparateProcess
    
    public function testSetUpUser()
    {
        // create a new user
        $user = User::factory()->create();

        // create a mock Request object 
        $request = new Request(['section' => 'Welcome']);
        $request->headers->set('Authorization', 'Bearer ' . $user->api_token);        

        // create a mock SitePageService object
        $sitePageService = new SitePageService();

        // create a mock UserService object
        $instruc = new App\Models\Instructor();
        $userService = new UserService($instruc);

        // create a new instance of the SitePageController
        $controller = new SitePageController($request, $sitePageService, $userService);
        // call the viewSitePage method with the request and section parameters
        $response = $controller->viewSitePage($request, 'Welcome');


        // assert that the response is a view
        $this->assertInstanceOf('Illuminate\View\View', $response);
    } */
}
?>