<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            // Free text, e.g. "Mon-Sat 9am-8pm" — shown to a customer choosing self-pickup.
            $table->string('pickup_hours')->nullable()->after('pickup_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropColumn('pickup_hours');
        });
    }
};
