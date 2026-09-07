<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\ProviderProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderCouponViewSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_coupons_page_renders_empty_and_populated(): void
    {
        $provider = ProviderProfile::factory()->sellsProducts()->create();

        $this->actingAs($provider->user)->get('/provider/coupons')->assertOk();

        Coupon::factory()->for($provider, 'providerProfile')->create(['code' => 'SMOKE10']);
        Coupon::factory()->for($provider, 'providerProfile')->flat(200)->expired()->create(['code' => 'OLDONE']);

        $this->actingAs($provider->user)->get('/provider/coupons')->assertOk()->assertSee('SMOKE10')->assertSee('OLDONE');
    }
}
