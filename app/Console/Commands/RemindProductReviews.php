<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\Notifier;
use Illuminate\Console\Command;

class RemindProductReviews extends Command
{
    protected $signature = 'products:remind-reviews';

    protected $description = 'Nudge consumers to rate the products from orders completed 7+ days ago';

    public function handle(Notifier $notifier): int
    {
        $orders = Order::query()
            ->where('status', Order::STATUS_COMPLETED)
            ->whereNotNull('completed_at')
            ->where('completed_at', '<=', now()->subDays(7))
            ->whereNull('review_reminder_sent_at')
            ->with('items.product', 'reviews', 'consumer')
            ->get();

        $sent = 0;

        foreach ($orders as $order) {
            $reviewable = $order->reviewableItems();

            // Nothing left to review (e.g. every item was already rated) — just mark it done and move on.
            if ($reviewable->isEmpty()) {
                $order->update(['review_reminder_sent_at' => now()]);
                continue;
            }

            $names = $reviewable->pluck('product_name')->take(3)->implode(', ');
            $body = $reviewable->count() > 1
                ? "How was {$names}" . ($reviewable->count() > 3 ? ' and more' : '') . "? Leave a quick rating to help other buyers."
                : "How was {$names}? Leave a quick rating to help other buyers.";

            $notifier->notify(
                $order->consumer,
                'product_review_reminder',
                'Rate your purchase',
                $body,
                route('consumer.orders.show', $order)
            );

            $order->update(['review_reminder_sent_at' => now()]);
            $sent++;
        }

        $this->info("Sent {$sent} review reminder(s).");

        return self::SUCCESS;
    }
}
