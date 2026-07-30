<?php

return [
    // Active gateway: mock | jazzcash | easypaisa
    'driver' => env('PAYMENT_DRIVER', 'mock'),

    // Escrow release mode: 'consumer_confirm' (consumer releases) or 'auto_on_complete'
    'release_mode' => env('PAYMENT_RELEASE_MODE', 'consumer_confirm'),

    // Smallest amount a provider can request as a single withdrawal.
    'min_withdrawal' => (float) env('MIN_WITHDRAWAL_AMOUNT', 500),

    // How many days a provider can carry an unpaid cash-commission debt
    // before they're automatically suspended from accepting new jobs.
    'settlement_grace_days' => (int) env('SETTLEMENT_GRACE_DAYS', 7),

    // Sahoulat's own account details, shown to a customer who picks "bank
    // transfer" after a booking completes — they send here, not to the
    // provider, and admin verifies against the real statement.
    'company_account' => [
        'bank_name' => env('COMPANY_BANK_NAME', 'Meezan Bank'),
        'account_title' => env('COMPANY_ACCOUNT_TITLE', 'Sahoulat Services'),
        'account_number' => env('COMPANY_ACCOUNT_NUMBER', ''),
        'iban' => env('COMPANY_IBAN', ''),
        'jazzcash_number' => env('COMPANY_JAZZCASH_NUMBER', ''),
        'easypaisa_number' => env('COMPANY_EASYPAISA_NUMBER', ''),
    ],

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