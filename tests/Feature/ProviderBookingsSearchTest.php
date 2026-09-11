<?php

namespace Tests\Feature;

use App\Models\ProviderProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression: the search box on the provider bookings index used a `Builder $q`
 * closure type-hint with no `use Illuminate\Database\Eloquent\Builder;` import —
 * harmless until someone actually typed a search term, at which point PHP threw a
 * TypeError the moment the closure ran (type-hints on closures resolve against the
 * *declaring* class's namespace, not the framework's), surfacing as a 500.
 */
class ProviderBookingsSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_can_search_bookings(): void
    {
        $provider = ProviderProfile::factory()->create();

        $this->actingAs($provider->user)
            ->get('/provider/bookings?q=test')
            ->assertOk();
    }
}
