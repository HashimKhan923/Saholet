<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationPollTest extends TestCase
{
    use RefreshDatabase;

    public function test_poll_returns_unread_count_and_latest_notifications(): void
    {
        $user = User::factory()->create();

        Notification::create(['user_id' => $user->id, 'type' => 'order', 'title' => 'Old', 'body' => 'Old one', 'read_at' => now()]);
        Notification::create(['user_id' => $user->id, 'type' => 'order', 'title' => 'New', 'body' => 'Unread one']);

        $response = $this->actingAs($user)->getJson(route('notifications.poll'));

        $response->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('latest.0.title', 'New');
    }
}
