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
        Schema::table('payments', function (Blueprint $table) {
            // Proof screenshot for the "bank transfer to Sahoulat" method, and the
            // admin verification trail before it's released to the provider.
            $table->string('screenshot_path')->nullable()->after('gateway_reference');
            $table->timestamp('verified_at')->nullable()->after('released_at');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
            $table->string('notes')->nullable()->after('verified_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn(['screenshot_path', 'verified_at', 'notes']);
        });
    }
};
