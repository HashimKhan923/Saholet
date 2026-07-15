<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_rate_limited_after_six_failed_attempts(): void
    {
        User::create([
            'name' => 'Real User',
            'email' => 'real.user@example.com',
            'phone' => '+923001234567',
            'role' => User::ROLE_CONSUMER,
            'password' => 'correct-password',
        ]);

        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/login', [
                'email' => 'real.user@example.com',
                'password' => 'wrong-password',
            ]);

            $response->assertStatus(302);
            $response->assertSessionHasErrors('email');
        }

        // 7th attempt within the same minute should be throttled, not just rejected.
        $response = $this->post('/login', [
            'email' => 'real.user@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
    }

    public function test_correct_password_still_logs_in_under_the_throttle_threshold(): void
    {
        $user = User::create([
            'name' => 'Real User',
            'email' => 'real.user2@example.com',
            'phone' => '+923001234568',
            'role' => User::ROLE_CONSUMER,
            'password' => 'correct-password',
        ]);

        $response = $this->post('/login', [
            'email' => 'real.user2@example.com',
            'password' => 'correct-password',
        ]);

        $response->assertRedirect(route($user->dashboardRoute()));
        $this->assertAuthenticatedAs($user);
    }
}
