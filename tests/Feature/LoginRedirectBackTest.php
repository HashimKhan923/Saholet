<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRedirectBackTest extends TestCase
{
    use RefreshDatabase;

    private function consumer(): User
    {
        return User::create([
            'name' => 'Guest Buyer', 'email' => 'guestbuyer@example.com', 'phone' => '+923001112233',
            'role' => User::ROLE_CONSUMER, 'password' => 'password',
        ]);
    }

    public function test_login_with_redirect_param_sends_the_user_back_to_that_page(): void
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create();
        $product = Product::factory()->for($provider, 'providerProfile')->create();
        $consumer = $this->consumer();

        $productUrl = route('shop.show', $product);

        $this->get('/login?redirect=' . urlencode('/shop/' . $product->id))->assertOk();

        $this->post('/login', ['email' => $consumer->email, 'password' => 'password'])
            ->assertRedirect($productUrl);
    }

    public function test_login_without_redirect_param_still_falls_back_to_dashboard(): void
    {
        $consumer = $this->consumer();

        $this->get('/login')->assertOk();

        $this->post('/login', ['email' => $consumer->email, 'password' => 'password'])
            ->assertRedirect(route('consumer.dashboard'));
    }

    public function test_an_absolute_external_redirect_param_is_ignored(): void
    {
        $consumer = $this->consumer();

        $this->get('/login?redirect=' . urlencode('https://evil.example.com/phish'))->assertOk();

        $this->post('/login', ['email' => $consumer->email, 'password' => 'password'])
            ->assertRedirect(route('consumer.dashboard'));
    }

    public function test_a_protocol_relative_redirect_param_is_ignored(): void
    {
        $consumer = $this->consumer();

        $this->get('/login?redirect=' . urlencode('//evil.example.com/phish'))->assertOk();

        $this->post('/login', ['email' => $consumer->email, 'password' => 'password'])
            ->assertRedirect(route('consumer.dashboard'));
    }

    public function test_auth_middleware_intended_redirect_still_works_without_a_redirect_param(): void
    {
        $consumer = $this->consumer();

        // Hitting a protected page while a guest already makes Laravel store
        // the intended URL automatically — confirm our change doesn't break that.
        $this->get('/cart')->assertRedirect('/login');

        $this->post('/login', ['email' => $consumer->email, 'password' => 'password'])
            ->assertRedirect('/cart');
    }
}
