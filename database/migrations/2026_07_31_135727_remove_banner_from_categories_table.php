<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (\DB::table('categories')->whereNotNull('banner')->pluck('banner') as $path) {
            Storage::disk('public')->delete($path);
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('banner');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('banner')->nullable()->after('image');
        });
    }
};
