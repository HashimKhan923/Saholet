<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city', 120);
            $table->decimal('latitude', 10, 7)->nullable();   // reference only in v1
            $table->decimal('longitude', 10, 7)->nullable();  // reference only in v1
            $table->unsignedInteger('radius_km')->nullable(); // reference only in v1
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_areas');
    }
};