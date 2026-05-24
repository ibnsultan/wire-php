# Wireblob PHP SDK

Server-side PHP SDK for [Wireblob](https://wireblob.com), a realtime platform for publishing events, authorizing private and presence channels, inspecting channel state, and validating webhooks.

This package is heavily aligned with the Pusher PHP server SDK and is intended to be Wireblob-friendly out of the box. If you are migrating from a Pusher-style integration, the API surface should feel familiar.

## Features

- Publish events to one or many channels
- Trigger batch events
- Authorize private, presence, and encrypted channels
- Fetch channel and presence information
- Validate and parse webhook payloads
- Keep compatibility with existing Pusher-style server integrations

## Requirements

- PHP `^7.3|^8.0`
- `ext-curl`
- `ext-json`

## Installation

```bash
composer require wireblob/wire
```

## Quick start

```php
<?php

use Wireblob\Wire;

$wire = new Wire(
    'your-application-key',
    'your-application-secret',
    'your-application-id',
    [
        'host' => 'eu-central-1.wireblob.com',
        'scheme' => 'https',
        'useTLS' => true,
    ]
);

$wire->trigger('chat-room', 'new-message', [
    'user' => 'Alice',
    'message' => 'Hello everyone!',
    'timestamp' => date(DATE_ATOM),
]);
```

## Configuration

The `Wire` constructor accepts your app credentials and an optional configuration array:

```php
$wire = new Wire($key, $secret, $appId, [
    'host' => 'eu-central-1.wireblob.com',
    'scheme' => 'https',
    'port' => 443,
    'path' => '',
    'timeout' => 30,
    'useTLS' => true,
    'cluster' => 'mt1',
    'encryption_master_key_base64' => 'base64-encoded-32-byte-key',
]);
```

### Common options

- `host`: Wireblob API host for your region
- `scheme`: `http` or `https`
- `port`: API port
- `timeout`: request timeout in seconds
- `useTLS`: when `true`, defaults to HTTPS on port `443`
- `cluster`: Pusher-style cluster fallback
- `encryption_master_key_base64`: required for encrypted channel authorization and webhook decryption

> For Wireblob deployments, you will usually want to set `host` explicitly.

## Publishing events

### Trigger a single event

```php
$wire->trigger('chat-room', 'new-message', [
    'user' => 'Alice',
    'message' => 'Hello everyone!',
]);
```

### Trigger to multiple channels

```php
$wire->trigger([
    'chat-room',
    'notifications',
], 'system-update', [
    'status' => 'ok',
]);
```

### Exclude the sender with `socket_id`

```php
$wire->trigger('chat-room', 'new-message', [
    'message' => 'Sent from the server',
], [
    'socket_id' => '1234.5678',
]);
```

### Trigger a batch of events

```php
$wire->triggerBatch([
    [
        'channel' => 'chat-room',
        'name' => 'new-message',
        'data' => ['message' => 'Hello chat'],
    ],
    [
        'channel' => 'notifications',
        'name' => 'ping',
        'data' => ['ok' => true],
    ],
]);
```

### Send an event directly to a user

```php
$wire->sendToUser('user-123', 'inbox-update', [
    'unread' => 4,
]);
```

## Channel authorization

Use the authorization helpers in your private or presence channel auth endpoint.

### Private channel

```php
$auth = $wire->authorizeChannel('private-chat-room', '1234.5678');

return response($auth, 200)
    ->header('Content-Type', 'application/json');
```

### Presence channel

```php
$auth = $wire->authorizePresenceChannel(
    'presence-chat-room',
    '1234.5678',
    'user-123',
    [
        'name' => 'Alice',
        'role' => 'member',
    ]
);
```

### Compatibility aliases

For projects coming from the Pusher SDK, the following compatibility methods are also available:

- `socketAuth()`
- `presenceAuth()`

The preferred method names are:

- `authorizeChannel()`
- `authorizePresenceChannel()`

## Channels and presence

```php
$channel = $wire->getChannelInfo('chat-room');
$channels = $wire->getChannels(['info' => 'connection_count']);
$users = $wire->getPresenceUsers('presence-chat-room');
```

## Webhooks

You can verify webhook signatures and parse webhook payloads from Wireblob.

```php
$headers = getallheaders();
$body = file_get_contents('php://input');

$webhook = $wire->webhook($headers, $body);

foreach ($webhook->get_events() as $event) {
    // Handle each event
}
```

If you only need to validate the signature:

```php
$wire->verifySignature($headers, $body);
```

## Async operations

Async variants are available when you want to work with Guzzle promises:

- `triggerAsync()`
- `triggerBatchAsync()`
- `sendToUserAsync()`
- `terminateUserConnectionsAsync()`

Example:

```php
$wire->triggerAsync('chat-room', 'new-message', [
    'message' => 'Queued from async flow',
])->then(function ($response) {
    // Success
});
```

## Error handling

The SDK may throw:

- `Wireblob\WireException` for validation, signing, or payload issues
- `Wireblob\ApiErrorException` when the API returns an error response
- `GuzzleHttp\Exception\GuzzleException` for HTTP client failures

Example:

```php
try {
    $wire->trigger('chat-room', 'new-message', ['message' => 'Hello']);
} catch (\Wireblob\ApiErrorException $e) {
    // API returned an error response
} catch (\Wireblob\WireException $e) {
    // SDK validation or signing error
}
```

## Notes for Pusher users

- The SDK API is intentionally close to the Pusher PHP server SDK
- Wireblob-specific deployments should use your Wireblob host, for example `eu-central-1.wireblob.com`
- Compatibility headers for Pusher-style integrations are still present in requests and webhook validation

## License

MIT
