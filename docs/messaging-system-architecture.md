# Messaging System Architecture

This document describes the Prasso messaging system's architecture, including how messages are created, queued, and delivered through various channels (email, SMS, push notifications).

## Table of Contents
1. [Overview](#overview)
2. [Database Schema](#database-schema)
3. [Message Creation Flow](#message-creation-flow)
4. [Queue Processing System](#queue-processing-system)
5. [Supported Channels](#supported-channels)
6. [Rate Limiting & Compliance](#rate-limiting--compliance)
7. [Configuration](#configuration)
8. [Monitoring & Troubleshooting](#monitoring--troubleshooting)

## Overview

The messaging system uses a **queued job architecture** for asynchronous message processing. When users compose and send messages through the admin interface, the system:

1. **Creates one message record** in `msg_messages` with content and metadata
2. **Creates individual delivery records** in `msg_deliveries` for each recipient
3. **Dispatches jobs** to process deliveries asynchronously
4. **Handles delivery through multiple channels** with comprehensive error handling

## Database Schema

### Messages Table (`msg_messages`)
```sql
CREATE TABLE msg_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    team_id BIGINT UNSIGNED NULL,
    type VARCHAR(255) NOT NULL, -- email, sms, push, inapp
    subject VARCHAR(255) NULL,
    body TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX msg_messages_team_id_index (team_id)
);
```

### Deliveries Table (`msg_deliveries`)
```sql
CREATE TABLE msg_deliveries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    team_id BIGINT UNSIGNED NULL,
    msg_message_id BIGINT UNSIGNED NOT NULL,
    recipient_type VARCHAR(255) NOT NULL, -- user | guest
    recipient_id BIGINT UNSIGNED NOT NULL,
    channel VARCHAR(255) NOT NULL, -- email | sms | push | inapp
    status VARCHAR(255) DEFAULT 'queued', -- queued | sent | delivered | failed | skipped
    provider_message_id VARCHAR(255) NULL,
    error TEXT NULL,
    metadata JSON NULL,
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    failed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX msg_deliveries_team_id_index (team_id),
    FOREIGN KEY (msg_message_id) REFERENCES msg_messages(id) ON DELETE CASCADE
);
```

## Message Creation Flow

### 1. User Interface (ComposeAndSendMessage.php)
- Located at `app/Filament/Pages/ComposeAndSendMessage.php`
- Form validates message type, subject, body, and recipients
- Creates one `MsgMessage` record with content
- Creates `MsgDelivery` records for each recipient
- Status: `'queued'`

### 2. Message Creation Code
```php
$message = MsgMessage::create([
    'team_id' => $team->id,
    'type' => $data['type'], // Required field
    'subject' => $data['subject'],
    'body' => $data['body'],
]);

// Create deliveries for each recipient
foreach ($recipients as $recipient) {
    MsgDelivery::create([
        'team_id' => $message->team_id,
        'msg_message_id' => $message->id,
        'recipient_type' => $recipientType,
        'recipient_id' => $recipient->id,
        'channel' => $message->type,
        'status' => 'queued',
    ]);
}
```

## Queue Processing System

### Job Processing Architecture

The system uses Laravel's queue system to process message deliveries asynchronously:

```bash
# Start queue workers
php artisan queue:work

# Or for specific queue
php artisan queue:work --queue=default
```

### ProcessMsgDelivery Job

Located at `packages/prasso/messaging/src/Jobs/ProcessMsgDelivery.php`

**Key Features:**
- **Retries with exponential backoff**: 60s, 120s, 300s, 600s
- **Rate limiting integration** with job dispatching
- **Per-attempt failure handling**
- **Channel-specific processing**

### Queue Drivers Supported
- **Sync** (immediate processing for development)
- **Database** (default production choice)
- **Redis** (high-performance)
- **SQS** (AWS)
- **Beanstalkd**

## Supported Channels

### 1. Email Channel
- Uses Laravel's `Mail` facade
- Supports token replacement: `{{FirstName}}`, `{{Name}}`
- Basic HTML/styled email support
- Tracks delivery status

### 2. SMS Channel
- **Twilio API integration**
- **Phone number validation and formatting**
- **Unicode support** with segment counting
- **SMS footer** for compliance (STOP instructions, opt-out)
- **Rate limiting per recipient**

### 3. Push Notifications
- Currently marked as "not implemented"
- Infrastructure ready for push notification providers

### 4. In-App Messages
- Designed for internal notifications
- User-specific delivery only

## Rate Limiting & Compliance

### Per-Guest Frequency Controls
```php
// Configurable limits in messaging.php
'rate_limit' => [
    'per_guest_monthly_cap' => 30,
    'per_guest_window_days' => 30,
    'allow_transactional_bypass' => true,
]
```

### Compliance Features
- **Opt-in/opt-out management**
- **Subscription status enforcement**
- **Do-not-contact lists**
- **Anonymous recipient handling**
- **Business/disclaimer footers** for SMS

### Team Verification Requirements
- **SMS sending requires verified team status**
- **Configurable per-team settings**
- **Verification audit trails**

## Configuration

### Queue Configuration (`config/queue.php`)
```php
'default' => env('QUEUE_CONNECTION', 'sync'),
'connections' => [
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
    ],
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
    ]
]
```

### Messaging Configuration (`config/messaging.php`)
```php
'rate_limit' => [
    'batch_size' => 50,
    'batch_interval_seconds' => 1,
    'per_guest_monthly_cap' => 30,
    'per_guest_window_days' => 30,
]
```

### Environment Variables
```env
QUEUE_CONNECTION=database
TWILIO_ACCOUNT_SID=your_sid
TWILIO_AUTH_TOKEN=your_token
TWILIO_PHONE_NUMBER=your_number
```

## Monitoring & Troubleshooting

### Key Monitoring Points
1. **Queue status**: `php artisan queue:status`
2. **Failed jobs**: Check `failed_jobs` table
3. **Delivery tracking**: Query `msg_deliveries` table
4. **Rate limiting**: Monitor per-guest counters

### Common Issues & Solutions

#### Issue: Messages stuck in "queued" status
**Root Cause:** Job dispatch missing or incorrect queue configuration
**Solutions:**
- Ensure `ProcessMsgDelivery::dispatch($delivery->id)` is called after creating delivery records (fixed in app/Filament/Pages/ComposeAndSendMessage.php)
- Set `QUEUE_CONNECTION=database` in `.env` file instead of `sync` for asynchronous processing
- Run queue worker: `php artisan queue:work --tries=3 --timeout=90`
- Check jobs table exists and migrations are run

#### Issue: Messages not sending
**Solution:** Ensure queue workers are running
```bash
php artisan queue:work --tries=3 --timeout=90
```

#### Issue: SMS failures
**Check:**
- Twilio credentials in environment
- Team verification status
- Recipient phone numbers
- Rate limits exceeded

#### Issue: Queue connection still set to sync
**Root Cause:** Environment variable not updated
**Solution:**
- Change `.env` file: `QUEUE_CONNECTION=database`
- Restart queue workers after configuration change
- Verify with: `php artisan tinker --execute="echo config('queue.default');"`

#### Issue: Database error "Field 'type' doesn't have a default value"
**Root Cause:** Missing `type` field in message creation
**Solution:** Ensure `type` is always provided when creating `MsgMessage` records

### Log Analysis
The system logs delivery attempts and failures to Laravel's default log channels:
```log
[2025-09-05 18:34:09] INFO: Message delivery created for user 123: Welcome Message
[2025-09-05 18:34:09] WARNING: SMS send error: Invalid phone number
```

### Database Queries for Monitoring
```sql
-- Check queued deliveries
SELECT COUNT(*) FROM msg_deliveries WHERE status = 'queued';

-- Check delivery success rate
SELECT channel, status, COUNT(*) as count
FROM msg_deliveries
GROUP BY channel, status;

-- Check recent failures
SELECT * FROM msg_deliveries
WHERE status IN ('failed', 'skipped')
AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR);
```

## Performance Considerations

### Batch Processing
- **Batch size**: Configurable delivery batching
- **Interval control**: Prevents API rate limiting
- **Memory management**: Large recipient lists handled in chunks

### Scaling Recommendations
1. **Separate queue workers** for message processing
2. **Redis queues** for high-volume applications
3. **Database optimization** on delivery tables
4. **Scheduled cleanup** of old delivery records

## API Integration

The messaging system also provides RESTful APIs for programmatic access:

### Send Message API
```http
POST /api/messages/send
{
  "message_id": 1,
  "user_ids": [1, 2, 3],
  "guest_ids": [10, 11]
}
```

### Create Message API
```http
POST /api/messages
{
  "subject": "Test Message",
  "body": "Message content",
  "type": "email"
}
```

## Conclusion

The messaging system is designed for high-volume, multi-channel communication with enterprise-grade reliability features including rate limiting, compliance controls, and comprehensive error handling. The queued architecture ensures scalability while maintaining per-message accountability through detailed delivery tracking.

For production deployment, ensure queue workers are running continuously and monitor failed job queues regularly.
