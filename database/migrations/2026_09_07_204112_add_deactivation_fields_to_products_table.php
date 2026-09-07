<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Set by an admin when they flip is_active off — shown to the provider so they know why, and what to fix.
            $table->text('deactivation_reason')->nullable()->after('is_active');
            $table->text('reactivation_instructions')->nullable()->after('deactivation_reason');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['deactivation_reason', 'reactivation_instructions']);
        });
    }
};
