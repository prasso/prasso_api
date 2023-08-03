<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendEmailsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $site_team_id;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and site_team_id for testing
        $this->user = User::factory()->create();
        $this->site_team_id = 1;
    }

    public function test_send_welcome_email()
    {
        // Create a mock mailer object
        $mailer = $this->getMockBuilder(\Illuminate\Contracts\Mail\Mailer::class)
                       ->disableOriginalConstructor()
                       ->getMock();

        // Set up expectations for the mailer object
        $mailer->expects($this->once())
               ->method('send')
               ->with('emails.welcome', ['user' => $this->user], $this->callback(function ($message) {
                   return $message->to[0]['address'] === $this->user->email &&
                          $message->subject === 'Welcome to our site!';
               }));

        // Set the mailer instance on the app container
        $this->app->instance(\Illuminate\Contracts\Mail\Mailer::class, $mailer);

        // Call the sendWelcomeEmail method on the user
        $this->user->sendWelcomeEmail($this->site_team_id);
    }
}