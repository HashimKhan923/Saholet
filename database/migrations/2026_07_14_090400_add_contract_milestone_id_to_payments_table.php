<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Loosen booking_id to nullable so a payment can instead belong to a
        // contract milestone. Done via raw DDL (no doctrine/dbal dependency).
        DB::statement('ALTER TABLE payments MODIFY booking_id BIGINT UNSIGNED NULL');

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('contract_milestone_id')->nullable()->after('booking_id')
                ->constrained()->cascadeOnDelete();

            $table->index(['contract_milestone_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('contract_milestone_id');
        });

        DB::statement('ALTER TABLE payments MODIFY booking_id BIGINT UNSIGNED NOT NULL');
    }
};
