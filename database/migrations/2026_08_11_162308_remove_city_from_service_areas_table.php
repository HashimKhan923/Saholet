<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_areas', function (Blueprint $table) {
            $table->dropIndex('service_areas_city_index');
            $table->dropColumn('city');
        });
    }

    public function down(): void
    {
        Schema::table('service_areas', function (Blueprint $table) {
            $table->string('city', 120)->nullable()->after('name');
            $table->index('city');
        });
    }
};
