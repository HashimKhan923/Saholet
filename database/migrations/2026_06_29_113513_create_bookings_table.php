<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('consumer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->date('scheduled_date');
            $table->time('scheduled_time');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('duration_minutes');
            $table->string('address');
            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // pending|confirmed|in_progress|completed|cancelled
            $table->string('cancelled_by')->nullable();    // consumer|provider
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['provider_profile_id', 'scheduled_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};