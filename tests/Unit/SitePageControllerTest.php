<?php
use Tests\TestCase;
use App\Http\Controllers\SitePageController;
use Illuminate\Http\Request;
use App\Services\SitePageService;
use App\Services\UserService;
use App\Models\User;

class SitePageControllerTest extends TestCase
{
      /**
     * @runInSeparateProcess
     */
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
    }
}
?>