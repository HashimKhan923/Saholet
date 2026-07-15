<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('resume_path');
            $table->string('resume_original_name');
            $table->text('cover_letter')->nullable();
            $table->string('status')->default('submitted'); // submitted|under_review|shortlisted|interview|rejected|hired|withdrawn
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['career_listing_id', 'user_id']);
            $table->index(['career_listing_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_applications');
    }
};
