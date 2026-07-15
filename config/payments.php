<?php

return [
    // Active gateway: mock | jazzcash | easypaisa
    'driver' => env('PAYMENT_DRIVER', 'mock'),

    // Escrow release mode: 'consumer_confirm' (consumer releases) or 'auto_on_complete'
    'release_mode' => env('PAYMENT_RELEASE_MODE', 'consumer_confirm'),

    // Smallest amount a provider can request as a single withdrawal.
    'min_withdrawal' => (float) env('MIN_WITHDRAWAL_AMOUNT', 500),

    'gateways' => [
        'mock' => [
            'label' => 'Test payment (sandbox)',
            'enabled' => true,
        ],
        'jazzcash' => [
            'label' => 'JazzCash',
            'enabled' => env('JAZZCASH_ENABLED', false),
            'merchant_id' => env('JAZZCASH_MERCHANT_ID'),
            'password' => env('JAZZCASH_PASSWORD'),
            'integrity_salt' => env('JAZZCASH_INTEGRITY_SALT'),
            'env' => env('JAZZCASH_ENV', 'sandbox'), // sandbox | production
        ],
        'easypaisa' => [
            'label' => 'Easypaisa',
            'enabled' => env('EASYPAISA_ENABLED', false),
            'store_id' => env('EASYPAISA_STORE_ID'),
            'account' => env('EASYPAISA_ACCOUNT'),
            'hash_key' => env('EASYPAISA_HASH_KEY'),
            'env' => env('EASYPAISA_ENV', 'sandbox'), // sandbox | production
        ],
    ],
];