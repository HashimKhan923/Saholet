<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('corporate_account_id')->nullable()->after('subscription_id')->constrained()->nullOnDelete();
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('corporate_account_id')->nullable()->after('consumer_id')->constrained()->nullOnDelete();
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('corporate_account_id')->nullable()->after('consumer_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('corporate_account_id');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('corporate_account_id');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('corporate_account_id');
        });
    }
};
