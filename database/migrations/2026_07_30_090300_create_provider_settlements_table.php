<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A provider paying down commission owed from cash jobs — the opposite
     * direction of a WithdrawalRequest. Provider submits how much they're
     * paying and how (in-person note, or bank transfer with a screenshot);
     * admin confirms the actual amount received (may be partial) or rejects.
     */
    public function up(): void
    {
        Schema::create('provider_settlements', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->string('method'); // cash | bank_transfer
            $table->decimal('amount', 12, 2); // what the provider claims to be paying
            $table->decimal('confirmed_amount', 12, 2)->nullable(); // what admin actually confirms received
            $table->string('screenshot_path')->nullable(); // bank_transfer proof
            $table->string('status')->default('pending'); // pending | confirmed | rejected
            $table->string('admin_notes')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['provider_profile_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_settlements');
    }
};
