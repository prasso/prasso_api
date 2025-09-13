<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\Request;
use Prasso\Messaging\Models\MsgGuest;
use Prasso\Messaging\Models\MsgConsentEvent;
use Prasso\Messaging\Models\MsgTeamSetting;
use Prasso\Messaging\Http\Controllers\Api\TwilioWebhookController;

class TwilioWebhookControllerTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        
    }

    public function testHandleOptInCreatesNewGuest()
    {
        // Create a unique phone number for this test to avoid conflicts with existing data
        $uniquePhone = '+1555' . rand(1000000, 9999999);
        
        // Make sure no guest exists with this phone number
        $hash = hash('sha256', preg_replace('/[^0-9]/', '', $uniquePhone));
        MsgGuest::where('phone_hash', $hash)->orWhere('phone', 'LIKE', "%$uniquePhone")->delete();
        
        // Count guests before the test
        $guestCountBefore = MsgGuest::count();
        $consentCountBefore = MsgConsentEvent::count();
        
        // Create controller instance
        $controller = new TwilioWebhookController();
        
        // Create a mock request
        $request = new Request([
            'From' => $uniquePhone,
            'Body' => 'START'
        ]);
        
        // Get the reflection method to access protected method
        $method = new \ReflectionMethod(TwilioWebhookController::class, 'handleOptIn');
        $method->setAccessible(true);
        
        // Call the method
        $result = $method->invoke($controller, $uniquePhone, $request, 'START');
        
        // Assert result is true (subscribed)
        $this->assertTrue($result);
        
        // Assert a new guest was created
        $this->assertEquals($guestCountBefore + 1, MsgGuest::count());
        
        // Get the created guest
        $guest = MsgGuest::where('phone', 'LIKE', "%$uniquePhone")->first();
        $this->assertNotNull($guest, 'Guest should be created with the test phone number');
        
        // Assert guest properties
        $this->assertNotNull($guest->team_id); // Just verify a team ID was assigned
        $this->assertEquals($uniquePhone, $guest->phone);
        $this->assertTrue($guest->is_subscribed);
        
        // Assert consent event was created
        $this->assertEquals($consentCountBefore + 1, MsgConsentEvent::count());
        $consentEvent = MsgConsentEvent::where('msg_guest_id', $guest->id)
            ->where('action', 'opt_in')
            ->first();
        $this->assertNotNull($consentEvent, 'Consent event should be created');
        $this->assertEquals('opt_in', $consentEvent->action);
        $this->assertEquals($guest->id, $consentEvent->msg_guest_id);
    }
    
    public function testHandleOptInWithExistingGuestRequiresRecentOptInRequest()
    {
        // Create a unique phone number for this test
        $uniquePhone = '+1555' . rand(1000000, 9999999);
        $uniqueEmail = 'test_guest_' . uniqid() . '@example.com';
        
        // Make sure no guest exists with this phone number
        $hash = hash('sha256', preg_replace('/[^0-9]/', '', $uniquePhone));
        MsgGuest::where('phone_hash', $hash)->orWhere('phone', 'LIKE', "%$uniquePhone")->delete();
        
        // Count consent events before the test
        $consentCountBefore = MsgConsentEvent::count();
        
        // Create a guest with unique identifiers
        $guest = MsgGuest::create([
            'team_id' => 1,
            'user_id' => 0, // Required by DB schema but not logically connected to a user
            'name' => 'Test Guest',
            'email' => $uniqueEmail,
            'phone' => $uniquePhone,
            'is_subscribed' => false
        ]);
        
        // Create controller instance
        $controller = new TwilioWebhookController();
        
        // Get the reflection method to access protected method
        $method = new \ReflectionMethod(TwilioWebhookController::class, 'handleOptIn');
        $method->setAccessible(true);
        
        // Call the method without a recent opt_in_request
        $result = $method->invoke($controller, $uniquePhone, null, 'START');
        
        // Assert result is false (not subscribed)
        $this->assertFalse($result);
        
        // Refresh guest from database
        $guest->refresh();
        
        // Assert guest is still not subscribed
        $this->assertFalse($guest->is_subscribed);
        
        // Now create a recent opt_in_request
        MsgConsentEvent::create([
            'team_id' => 1,
            'msg_guest_id' => $guest->id,
            'action' => 'opt_in_request',
            'method' => 'web',
            'source' => 'test',
            'occurred_at' => now()
        ]);
        
        // Call the method again
        $result = $method->invoke($controller, $uniquePhone, null, 'START');
        
        // Assert result is true (subscribed)
        $this->assertTrue($result);
        
        // Refresh guest from database
        $guest->refresh();
        
        // Assert guest is now subscribed
        $this->assertTrue($guest->is_subscribed);
        
        // Verify consent events were created
        $this->assertGreaterThan($consentCountBefore, MsgConsentEvent::count(), 'New consent events should be created');
    }
}
