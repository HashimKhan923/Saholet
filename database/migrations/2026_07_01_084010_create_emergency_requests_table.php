<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('consumer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('address');
            $table->string('city', 120);
            $table->text('notes')->nullable();
            $table->string('status')->default('open'); // open | matched | cancelled
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('matched_provider_profile_id')->nullable()->constrained('provider_profiles')->nullOnDelete();
            $table->timestamp('matched_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['service_id', 'status']);
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_requests');
    }
};