<?php

namespace Tests\Feature;

use App\Models\CareerApplication;
use App\Models\CareerCategory;
use App\Models\CareerListing;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CareerApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_seeker_can_upload_a_resume_and_apply_for_an_open_listing(): void
    {
        Storage::fake('local');

        $category = CareerCategory::create(['name' => 'Skilled Trades', 'slug' => 'skilled-trades', 'is_active' => true]);
        $admin = User::create([
            'name' => 'HR Admin', 'email' => 'hr.admin@example.com', 'phone' => '+923007777777',
            'role' => User::ROLE_ADMIN, 'password' => 'password',
        ]);
        $listing = CareerListing::create([
            'career_category_id' => $category->id,
            'title' => 'Site Electrician',
            'slug' => 'site-electrician',
            'description' => 'Wiring for residential sites.',
            'employment_type' => 'full_time',
            'city' => 'Lahore',
            'status' => CareerListing::STATUS_OPEN,
            'posted_by' => $admin->id,
        ]);

        $jobSeeker = User::create([
            'name' => 'Job Seeker', 'email' => 'job.seeker@example.com', 'phone' => '+923008888888',
            'role' => User::ROLE_JOB_SEEKER, 'password' => 'password',
        ]);

        $this->actingAs($jobSeeker)
            ->post('/job-seeker/profile/resume', ['resume' => UploadedFile::fake()->create('resume.pdf', 200, 'application/pdf')])
            ->assertRedirect();

        $this->assertDatabaseHas('job_seeker_profiles', ['user_id' => $jobSeeker->id]);

        $this->actingAs($jobSeeker)
            ->post("/job-seeker/careers/{$listing->id}/apply", ['cover_letter' => 'I would love to join your team.'])
            ->assertRedirect();

        $application = CareerApplication::firstOrFail();
        $this->assertSame($jobSeeker->id, $application->user_id);
        $this->assertSame($listing->id, $application->career_listing_id);
        $this->assertSame(CareerApplication::STATUS_SUBMITTED, $application->status);
        $this->assertNotNull($application->resume_path);
        Storage::disk('local')->assertExists($application->resume_path);

        // Duplicate applications to the same listing must be rejected.
        $this->actingAs($jobSeeker)
            ->post("/job-seeker/careers/{$listing->id}/apply", ['cover_letter' => 'Again please.'])
            ->assertRedirect();

        $this->assertSame(1, CareerApplication::where('career_listing_id', $listing->id)->where('user_id', $jobSeeker->id)->count());
    }

    public function test_application_without_a_resume_on_file_requires_an_upload(): void
    {
        $category = CareerCategory::create(['name' => 'Trades', 'slug' => 'trades', 'is_active' => true]);
        $admin = User::create([
            'name' => 'HR Admin Two', 'email' => 'hr.admin2@example.com', 'phone' => '+923009999999',
            'role' => User::ROLE_ADMIN, 'password' => 'password',
        ]);
        $listing = CareerListing::create([
            'career_category_id' => $category->id,
            'title' => 'Plumber',
            'slug' => 'plumber',
            'description' => 'Residential plumbing.',
            'employment_type' => 'contract',
            'status' => CareerListing::STATUS_OPEN,
            'posted_by' => $admin->id,
        ]);

        $jobSeeker = User::create([
            'name' => 'No Resume Seeker', 'email' => 'no.resume@example.com', 'phone' => '+923000001111',
            'role' => User::ROLE_JOB_SEEKER, 'password' => 'password',
        ]);

        $this->actingAs($jobSeeker)
            ->post("/job-seeker/careers/{$listing->id}/apply", ['cover_letter' => 'Please consider me.'])
            ->assertSessionHasErrors('resume');

        $this->assertSame(0, CareerApplication::count());
    }
}
