<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->string('payout_method')->nullable()->after('cnic_number'); // bank | jazzcash | easypaisa
            $table->string('payout_account_title')->nullable()->after('payout_method');
            $table->string('payout_account_number')->nullable()->after('payout_account_title');
            $table->string('payout_bank_name')->nullable()->after('payout_account_number');
        });
    }

    public function down(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropColumn(['payout_method', 'payout_account_title', 'payout_account_number', 'payout_bank_name']);
        });
    }
};
