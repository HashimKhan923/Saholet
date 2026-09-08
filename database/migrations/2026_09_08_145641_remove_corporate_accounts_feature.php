<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('corporate_account_id');
            $table->dropColumn('corporate_role');
        });

        Schema::dropIfExists('corporate_accounts');
    }

    public function down(): void
    {
        Schema::create('corporate_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('billing_email');
            $table->string('billing_phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('corporate_account_id')->nullable()->after('credit_balance')->constrained()->nullOnDelete();
            $table->string('corporate_role')->nullable()->after('corporate_account_id');
        });

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
};
