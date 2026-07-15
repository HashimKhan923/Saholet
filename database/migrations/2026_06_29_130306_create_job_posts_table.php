<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('consumer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->text('description');
            $table->decimal('budget', 10, 2)->nullable();
            $table->date('preferred_date')->nullable();
            $table->string('address');
            $table->string('city', 120);
            $table->string('status')->default('open'); // open | awarded | cancelled
            $table->timestamp('awarded_at')->nullable();
            $table->timestamps();

            $table->index(['service_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};