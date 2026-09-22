<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VideoReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminVideoReviewManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin', 'email' => 'admin@example.com', 'phone' => '+923001112222',
            'role' => User::ROLE_ADMIN, 'password' => 'password',
        ]);
    }

    private function staff(array $permissions = []): User
    {
        return User::create([
            'name' => 'Staff', 'email' => 'staff@example.com', 'phone' => '+923009998888',
            'role' => User::ROLE_STAFF, 'password' => 'password', 'permissions' => $permissions,
        ]);
    }

    public function test_admin_can_create_a_video_review_from_a_youtube_watch_url(): void
    {
        $response = $this->actingAs($this->admin())->post('/admin/video-reviews', [
            'title' => 'Happy customer in Karachi',
            'description' => 'AC repair done same day.',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=5s',
            'sort_order' => 1,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.video-reviews.index'));

        $video = VideoReview::firstOrFail();
        $this->assertSame('dQw4w9WgXcQ', $video->youtube_id);
        $this->assertTrue($video->is_active);
    }

    public function test_admin_can_create_a_video_review_from_a_short_youtu_be_url(): void
    {
        $this->actingAs($this->admin())->post('/admin/video-reviews', [
            'title' => 'Quick review',
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'sort_order' => 0,
        ])->assertRedirect();

        $this->assertSame('dQw4w9WgXcQ', VideoReview::firstOrFail()->youtube_id);
    }

    public function test_invalid_youtube_url_is_rejected(): void
    {
        $this->actingAs($this->admin())->post('/admin/video-reviews', [
            'title' => 'Bad link',
            'youtube_url' => 'https://example.com/not-a-video',
            'sort_order' => 0,
        ])->assertSessionHasErrors('youtube_url');

        $this->assertSame(0, VideoReview::count());
    }

    public function test_admin_can_update_and_delete_a_video_review(): void
    {
        $video = VideoReview::create([
            'title' => 'Old title', 'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'youtube_id' => 'dQw4w9WgXcQ', 'sort_order' => 0, 'is_active' => true,
        ]);
        $admin = $this->admin();

        $this->actingAs($admin)->put("/admin/video-reviews/{$video->id}", [
            'title' => 'New title',
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'sort_order' => 2,
            'is_active' => 0,
        ])->assertRedirect();

        $video->refresh();
        $this->assertSame('New title', $video->title);
        $this->assertFalse($video->is_active);

        $this->actingAs($admin)->delete("/admin/video-reviews/{$video->id}")->assertRedirect();
        $this->assertSame(0, VideoReview::count());
    }

    public function test_staff_without_the_permission_is_forbidden(): void
    {
        $this->actingAs($this->staff())->get('/admin/video-reviews')->assertForbidden();
    }

    public function test_staff_with_the_permission_can_manage_video_reviews(): void
    {
        $staff = $this->staff(['video_reviews' => ['view', 'create', 'edit', 'delete']]);

        $this->actingAs($staff)->get('/admin/video-reviews')->assertOk();

        $this->actingAs($staff)->post('/admin/video-reviews', [
            'title' => 'Staff-added video',
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'sort_order' => 0,
        ])->assertRedirect();

        $this->assertSame(1, VideoReview::count());
    }
}
