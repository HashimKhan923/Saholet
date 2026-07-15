<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('corporate_account_id')->nullable()->after('credit_balance')->constrained()->nullOnDelete();
            $table->string('corporate_role')->nullable()->after('corporate_account_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('corporate_account_id');
            $table->dropColumn('corporate_role');
        });
    }
};
