<?php

namespace Tests\Feature;

use App\Models\VideoReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingVideoReviewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_active_video_reviews_and_hides_inactive_ones(): void
    {
        VideoReview::create([
            'title' => 'Visible testimonial', 'description' => 'Great service',
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ', 'youtube_id' => 'dQw4w9WgXcQ',
            'sort_order' => 0, 'is_active' => true,
        ]);
        VideoReview::create([
            'title' => 'Hidden testimonial',
            'youtube_url' => 'https://youtu.be/oHg5SJYRHA0', 'youtube_id' => 'oHg5SJYRHA0',
            'sort_order' => 1, 'is_active' => false,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Visible testimonial');
        $response->assertDontSee('Hidden testimonial');
        $response->assertSee(__('messages.landing.video_reviews_title'));
    }

    public function test_homepage_omits_the_video_section_when_there_are_no_videos(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee(__('messages.landing.video_reviews_title'));
    }

    public function test_a_newly_added_video_shows_up_on_the_very_next_homepage_load(): void
    {
        $this->get('/')->assertDontSee(__('messages.landing.video_reviews_title'));

        VideoReview::create([
            'title' => 'Just uploaded', 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'youtube_id' => 'dQw4w9WgXcQ', 'sort_order' => 0, 'is_active' => true,
        ]);

        $this->get('/')->assertSee('Just uploaded');
    }
}
