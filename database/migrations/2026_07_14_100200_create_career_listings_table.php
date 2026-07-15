<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_category_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->string('employment_type')->default('full_time'); // full_time|part_time|contract|internship
            $table->string('city', 120)->nullable();
            $table->boolean('is_remote')->default(false);
            $table->decimal('salary_min', 10, 2)->nullable();
            $table->decimal('salary_max', 10, 2)->nullable();
            $table->string('status')->default('draft'); // draft|open|closed|filled
            $table->foreignId('posted_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('closes_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'career_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_listings');
    }
};
