<?php

require __DIR__.'/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Prasso\Messaging\Models\MsgMessage;
use Prasso\Messaging\Models\MsgDelivery;
use Prasso\Messaging\Jobs\ProcessMsgDelivery;
use Prasso\Church\Models\Member;

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$argvMemberId = $argv[1] ?? null;
$channel = $argv[2] ?? 'sms'; // sms | email
$teamId = isset($argv[3]) ? (int) $argv[3] : null; // optional team id

if (!$argvMemberId) {
    echo "Usage: php test-message-sending-members.php <member_id> [channel=sms|email] [team_id]\n";
    exit(1);
}

$member = Member::query()->find($argvMemberId);
if (!$member) {
    echo "Member not found: ID {$argvMemberId}\n";
    exit(1);
}

if ($channel === 'sms' && empty($member->phone)) {
    echo "Member has no phone for SMS: ID {$member->id}\n";
    exit(1);
}
if ($channel === 'email' && empty($member->email)) {
    echo "Member has no email for Email: ID {$member->id}\n";
    exit(1);
}

echo "Creating test {$channel} message for member {$member->id} ({$member->full_name})...\n";

$message = MsgMessage::create([
    'team_id' => $teamId,
    'type' => $channel,
    'subject' => $channel === 'email' ? 'Test Email to Member' : 'Test SMS to Member',
    'body' => $channel === 'email'
        ? 'Hello {{FirstName}}, this is a test email from Messaging.'
        : 'Hello {{FirstName}}, this is a test SMS from Messaging.',
]);

echo "Created message ID: {$message->id}\n";

$delivery = MsgDelivery::create([
    'team_id' => $teamId,
    'msg_message_id' => $message->id,
    'recipient_type' => 'member',
    'recipient_id' => $member->id,
    'channel' => $channel,
    'status' => 'queued',
    'metadata' => [
        'subject' => $message->subject,
        'preview' => mb_substr($message->body, 0, 120),
        'type' => 'test',
    ],
]);

echo "Created delivery ID: {$delivery->id}\n";

$job = new ProcessMsgDelivery($delivery->id);
$job->handle();

echo "Processed delivery.\n";
$delivery->refresh();
echo "Status: {$delivery->status}\n";
if ($delivery->error) {
    echo "Error: {$delivery->error}\n";
} else {
    echo "Provider SID: {$delivery->provider_message_id}\n";
}
