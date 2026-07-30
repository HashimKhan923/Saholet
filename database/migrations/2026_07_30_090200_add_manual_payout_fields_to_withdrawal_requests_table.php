<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            // How this particular request was actually fulfilled — distinct from
            // payout_method (the provider's saved bank/wallet details) since a
            // provider can instead just pick the cash up at the office.
            $table->string('fulfillment_method')->nullable()->after('payout_bank_name'); // cash_pickup | bank_transfer
            // Admin's proof of transfer for the bank_transfer path. Status moves to
            // 'awaiting_confirmation' once this is attached, and only becomes
            // 'paid' once the provider confirms receipt below — cash_pickup skips
            // straight to 'paid' since it's handed over in person.
            $table->string('screenshot_path')->nullable()->after('fulfillment_method');
            $table->timestamp('provider_confirmed_at')->nullable()->after('processed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropColumn(['fulfillment_method', 'screenshot_path', 'provider_confirmed_at']);
        });
    }
};
