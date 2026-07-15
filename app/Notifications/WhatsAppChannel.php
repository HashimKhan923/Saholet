<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Meta WhatsApp Cloud API (graph.facebook.com) — free-tier accessible directly
 * from a Meta Business account, no Pakistani BSP reseller required.
 *
 * Note: Meta only allows freeform text messages within a 24-hour customer
 * service window (i.e. the recipient messaged the business number recently).
 * Business-initiated notifications outside that window — which is most of
 * what Sahoulet sends — require a pre-approved message template instead of
 * plain text. This channel sends plain text, which will work in the sandbox/
 * test-number setup; swap the payload for a `template` message once you have
 * approved templates for production.
 */
class WhatsAppChannel implements NotificationChannel
{
    public function key(): string
    {
        return 'whatsapp';
    }

    public function label(): string
    {
        return 'WhatsApp';
    }

    public function isAvailable(): bool
    {
        $cfg = config('notifications.channels.whatsapp');

        return ! empty($cfg['phone_number_id']) && ! empty($cfg['access_token']);
    }

    public function deliver(User $recipient, array $payload): void
    {
        if (! $this->isAvailable()) {
            Log::warning('[notify:whatsapp] WhatsApp channel enabled but not configured — skipped.', [
                'to' => $recipient->phone,
                'title' => $payload['title'],
            ]);
            return;
        }

        if (blank($recipient->phone)) {
            return;
        }

        $cfg = config('notifications.channels.whatsapp');
        $message = "*{$payload['title']}*\n" . $payload['body'] . ($payload['url'] ? "\n" . $payload['url'] : '');

        $response = Http::timeout(10)
            ->withToken($cfg['access_token'])
            ->post("https://graph.facebook.com/v20.0/{$cfg['phone_number_id']}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $this->e164($recipient->phone),
                'type' => 'text',
                'text' => ['body' => $message],
            ]);

        if (! $response->successful()) {
            Log::warning('[notify:whatsapp] Cloud API rejected the message', [
                'to' => $recipient->phone,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }

    /**
     * WhatsApp Cloud API wants digits only, no leading +. Numbers are stored
     * with the country code already included (e.g. "+92 3XX XXXXXXX" per the
     * registration form) — this just strips formatting, not the country code.
     */
    private function e164(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }
}
