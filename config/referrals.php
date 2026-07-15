<?php

return [
    // PKR credited to each side when a referred user's first payment clears escrow.
    'referrer_reward' => (float) env('REFERRAL_REFERRER_REWARD', 500),
    'referred_reward' => (float) env('REFERRAL_REFERRED_REWARD', 250),
];
