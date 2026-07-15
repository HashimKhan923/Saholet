<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('proposed_date');
            $table->time('proposed_time');
            $table->text('message')->nullable();
            $table->string('status')->default('pending'); // pending | accepted | rejected | withdrawn
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['job_post_id', 'provider_profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bids');
    }
};