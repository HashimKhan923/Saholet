<?php

namespace App\Notifications;

class NotificationManager
{
    /** @var array<string, NotificationChannel> */
    protected array $channels = [];

    /** @param iterable<NotificationChannel> $channels */
    public function __construct(iterable $channels)
    {
        foreach ($channels as $channel) {
            $this->channels[$channel->key()] = $channel;
        }
    }

    /** @return array<int, NotificationChannel> */
    public function all(): array
    {
        return array_values($this->channels);
    }

    /** Channels toggled on in config. @return array<int, NotificationChannel> */
    public function enabled(): array
    {
        return array_values(array_filter(
            $this->channels,
            fn (NotificationChannel $c) => (bool) config("notifications.channels.{$c->key()}.enabled", false)
        ));
    }
}