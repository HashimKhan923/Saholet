<?php

return [
    // In-app (database) notifications are always written. These are the extra fan-out channels.
    'channels' => [
        'log' => [
            'enabled' => env('NOTIFY_LOG', true),
        ],
        'mail' => [
            'enabled' => env('NOTIFY_MAIL', false),
        ],
        'sms' => [
            'enabled' => env('NOTIFY_SMS', false),
            'gateway' => env('SMS_GATEWAY'),
            'api_key' => env('SMS_API_KEY'),
            'sender' => env('SMS_SENDER'),
        ],
        'whatsapp' => [
            'enabled' => env('NOTIFY_WHATSAPP', false),
            'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
            'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
        ],
        'push' => [
            'enabled' => env('NOTIFY_PUSH', false),
            'vapid_public_key' => env('VAPID_PUBLIC_KEY'),
            'vapid_private_key' => env('VAPID_PRIVATE_KEY'),
            'vapid_subject' => env('VAPID_SUBJECT', 'mailto:support@example.com'),
        ],
    ],
];