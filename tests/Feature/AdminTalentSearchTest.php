<?php

namespace Tests\Feature;

use App\Models\JobSeekerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTalentSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_candidates_by_skill_and_city(): void
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin@example.com', 'phone' => '+923008888888',
            'role' => User::ROLE_ADMIN, 'password' => 'password',
        ]);

        $matching = User::create([
            'name' => 'Ali Electrician', 'email' => 'ali@example.com', 'phone' => '+923009999999',
            'role' => User::ROLE_JOB_SEEKER, 'password' => 'password',
        ]);
        JobSeekerProfile::create([
            'user_id' => $matching->id, 'headline' => 'Senior Electrician', 'city' => 'Karachi',
            'experience_years' => 5, 'skills' => ['electrician', 'wiring'],
            'resume_path' => 'resumes/ali.pdf', 'resume_original_name' => 'ali.pdf',
            'resume_mime_type' => 'application/pdf', 'resume_size' => 1000, 'resume_uploaded_at' => now(),
        ]);

        $nonMatching = User::create([
            'name' => 'Sara Plumber', 'email' => 'sara@example.com', 'phone' => '+923001010101',
            'role' => User::ROLE_JOB_SEEKER, 'password' => 'password',
        ]);
        JobSeekerProfile::create([
            'user_id' => $nonMatching->id, 'headline' => 'Plumber', 'city' => 'Lahore',
            'experience_years' => 1, 'skills' => ['plumbing'],
            'resume_path' => 'resumes/sara.pdf', 'resume_original_name' => 'sara.pdf',
            'resume_mime_type' => 'application/pdf', 'resume_size' => 1000, 'resume_uploaded_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/talent?skill=electrician&city=Karachi');

        $response->assertOk();
        $response->assertSee('Ali Electrician');
        $response->assertDontSee('Sara Plumber');
    }
}
