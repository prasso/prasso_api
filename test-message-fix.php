<?php

require __DIR__.'/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Prasso\Messaging\Models\MsgMessage;
use Prasso\Messaging\Models\MsgDelivery;
use Prasso\Messaging\Jobs\ProcessMsgDelivery;

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting message sending test with fixed code...\n";

try {
    // Create a test message
    $message = MsgMessage::create([
        'team_id' => 1, // Use an existing team ID
        'type' => 'sms',
        'subject' => 'Test Message After Fix',
        'body' => 'This is a test message to verify the ProcessMsgDelivery job is working correctly after fixing the logCtx issue.',
    ]);
    
    echo "Created test message with ID: {$message->id}\n";
    
    // Create a delivery record
    $delivery = MsgDelivery::create([
        'team_id' => $message->team_id,
        'msg_message_id' => $message->id,
        'recipient_type' => 'user',
        'recipient_id' => 1, // Use an existing user ID
        'channel' => 'sms',
        'status' => 'queued',
        'metadata' => [
            'subject' => $message->subject,
            'preview' => substr($message->body, 0, 120),
            'type' => 'test',
        ],
    ]);
    
    echo "Created test delivery with ID: {$delivery->id}\n";
    
    // Process the delivery synchronously for testing
    $job = new ProcessMsgDelivery($delivery->id);
    $job->handle();
    
    echo "Processed delivery job\n";
    
    // Refresh the delivery record to see the updated status
    $delivery->refresh();
    echo "Delivery status: {$delivery->status}\n";
    if ($delivery->error) {
        echo "Error: {$delivery->error}\n";
    } else {
        echo "Success! The message was processed without errors.\n";
    }
    
} catch (\Throwable $e) {
    echo "Error: {$e->getMessage()}\n";
    echo $e->getTraceAsString() . "\n";
}

echo "Test completed.\n";
