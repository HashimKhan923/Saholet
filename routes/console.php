<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// AMC/subscription plans: create each visit's Booking once it falls due.
// Requires `php artisan schedule:run` on a real cron entry in production.
Schedule::command('subscriptions:generate-due-visits')->dailyAt('06:00');

// Process queued notification deliveries (email/SMS/WhatsApp/push) every minute.
// Piggybacks on the same cron entry above — no separate `queue:work` daemon needed.
Schedule::command('queue:work --stop-when-empty --tries=3 --max-time=50')
    ->everyMinute()
    ->withoutOverlapping();
