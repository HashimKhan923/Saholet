<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('credit_applied');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by');
            $table->dropColumn(['referral_code', 'credit_balance']);
        });

        Schema::dropIfExists('referral_rewards');
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 12)->nullable()->unique()->after('role');
            $table->foreignId('referred_by')->nullable()->after('referral_code')->constrained('users')->nullOnDelete();
            $table->decimal('credit_balance', 10, 2)->default(0)->after('referred_by');
        });

        Schema::create('referral_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->decimal('referrer_reward', 10, 2);
            $table->decimal('referred_reward', 10, 2);
            $table->timestamps();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('credit_applied', 10, 2)->default(0)->after('amount');
        });
    }
};
