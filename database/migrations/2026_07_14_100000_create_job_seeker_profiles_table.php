<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_seeker_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('headline')->nullable();
            $table->text('bio')->nullable();
            $table->string('city', 120)->nullable();
            $table->unsignedInteger('experience_years')->default(0);
            $table->string('current_position')->nullable();
            $table->json('skills')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('resume_path')->nullable();
            $table->string('resume_original_name')->nullable();
            $table->string('resume_mime_type')->nullable();
            $table->unsignedInteger('resume_size')->nullable();
            $table->timestamp('resume_uploaded_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_seeker_profiles');
    }
};
