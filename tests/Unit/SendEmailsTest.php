<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App\Http\Controllers\Controller;
use App\Mail\welcome_user;
use App\Mail\prasso_user_welcome;

class SendEmailsTest extends TestCase
{

    protected $user;
    protected $site_team_id;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and site_team_id for testing
        $this->user = User::factory()->create();
        $this->user->email = 'test@example.com';
        $this->site_team_id = 1;
    }

    public function test_get_client_from_host()
    {
        // Create a mock request object with a specific host name
        $request = Request::create('http://localhost:8000', 'GET');
        $host = request()->getHttpHost();

        // Call the getClientFromHost method on the controller
        $controller = new Controller($request);
        $client = $controller->getClientFromHost();
          // Assert that the correct client was returned
    $this->assertStringContainsString('localhost', $client);

    }

    public function test_send_welcome_email()
{
    // Set up expectations for the Mail facade
    Mail::fake();
    info('test_send_welcome_email: '.$this->user->email);
    Mail::assertNothingSent();

    Mail::to($this->user->email)->send(new welcome_user($this->user));

    Mail::assertSent(welcome_user::class);

}

public function test_admin_welcome_user(): void
{

    $newUserEmail = new prasso_user_welcome($this->user,1);
    $html            = $newUserEmail->render();

    $this->assertTrue(strpos($html, 'Prasso Local') !== false);
}
}