<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_areas', function (Blueprint $table) {
            // Array of {lat, lng} vertices — nullable so existing/new city-only
            // areas keep working via the legacy string-match fallback.
            $table->json('boundary')->nullable()->after('radius_km');
        });
    }

    public function down(): void
    {
        Schema::table('service_areas', function (Blueprint $table) {
            $table->dropColumn('boundary');
        });
    }
};
