<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('consumer_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('address');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('city', 120);
            $table->date('preferred_start_date')->nullable();
            $table->string('status')->default('submitted'); // submitted|quoted|accepted|rejected|in_progress|completed|cancelled
            $table->decimal('quoted_total', 12, 2)->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('quoted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('quoted_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index(['consumer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
