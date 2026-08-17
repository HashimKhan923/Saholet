<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class JobSeekerProfile extends Model
{
    /** Pre-defined so admins reviewing candidates get a consistent, filterable value instead of free text. */
    public const QUALIFICATIONS = [
        'middle' => 'Middle',
        'matric' => 'Matric / Secondary (SSC)',
        'intermediate' => 'Intermediate (HSSC)',
        'diploma' => 'Diploma / Vocational Certificate',
        'bachelors' => "Bachelor's",
        'masters' => "Master's",
        'mphil' => 'MPhil',
        'phd' => 'PhD',
    ];

    protected $fillable = [
        'user_id',
        'headline',
        'bio',
        'city',
        'address',
        'experience_years',
        'current_position',
        'qualification',
        'skills',
        'linkedin_url',
        'resume_path',
        'resume_original_name',
        'resume_mime_type',
        'resume_size',
        'resume_uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'experience_years' => 'integer',
            'skills' => 'array',
            'resume_uploaded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasResume(): bool
    {
        return $this->resume_path !== null;
    }

    public function resumeUrl(): ?string
    {
        return $this->resume_path ? route('job-seeker.resume.show', $this) : null;
    }

    public function deleteResumeFile(): void
    {
        if ($this->resume_path) {
            Storage::disk(config('careers.disk'))->delete($this->resume_path);
        }
    }
}
