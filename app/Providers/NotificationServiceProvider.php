<?php

namespace App\Providers;

use App\Models\Notification;
use App\Notifications\LogChannel;
use App\Notifications\MailChannel;
use App\Notifications\NotificationManager;
use App\Notifications\PushChannel;
use App\Notifications\SmsChannel;
use App\Notifications\WhatsAppChannel;
use App\Services\Notifier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NotificationManager::class, function () {
            return new NotificationManager([
                new LogChannel(),
                new MailChannel(),
                new SmsChannel(),
                new WhatsAppChannel(),
                new PushChannel(),
            ]);
        });

        $this->app->singleton(Notifier::class, fn () => new Notifier());
    }

    public function boot(): void
    {
        // Share the unread count + a recent-notifications seed wherever the live notification bell renders
        // (public layout header, admin portal, provider portal — all use the same component).
        View::composer('components.notification-bell', function ($view) {
            $count = 0;
            $recent = collect();

            if (Auth::check()) {
                $count = Notification::where('user_id', Auth::id())->whereNull('read_at')->count();
                $recent = Notification::where('user_id', Auth::id())
                    ->latest()
                    ->limit(10)
                    ->get(['id', 'type', 'title', 'body', 'url', 'read_at', 'created_at']);
            }

            $view->with('unreadNotifications', $count);
            $view->with('recentNotifications', $recent);
        });
    }
}