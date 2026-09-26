<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\VideoReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingBrandsTest extends TestCase
{
    use RefreshDatabase;

    private function brand(string $name, int $order = 0, bool $active = true): Brand
    {
        return Brand::create(['name' => $name, 'logo' => "brands/{$name}.png", 'sort_order' => $order, 'is_active' => $active]);
    }

    public function test_homepage_shows_active_brands_in_sort_order_and_hides_hidden_ones(): void
    {
        $this->brand('Zeta Corp', 2);
        $this->brand('Alpha Corp', 1);
        $this->brand('Hidden Corp', 0, false);

        $this->get('/')
            ->assertOk()
            ->assertSee(__('messages.landing.brands_title'))
            ->assertSeeInOrder(['Alpha Corp', 'Zeta Corp'])
            ->assertDontSee('Hidden Corp');
    }

    public function test_the_brands_section_is_hidden_when_there_are_no_brands(): void
    {
        $this->get('/')->assertOk()->assertDontSee(__('messages.landing.brands_title'));
    }

    public function test_brands_render_below_the_review_videos_and_a_new_brand_shows_immediately(): void
    {
        VideoReview::create([
            'title' => 'A review', 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'youtube_id' => 'dQw4w9WgXcQ', 'sort_order' => 0, 'is_active' => true,
        ]);
        $this->get('/')->assertDontSee(__('messages.landing.brands_title'));

        $this->brand('Fresh Brand');

        $this->get('/')->assertSeeInOrder([__('messages.landing.video_reviews_title'), __('messages.landing.brands_title'), 'Fresh Brand']);
    }
}
