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
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('visit_charge_amount', 10, 2)->nullable()->after('cancellation_reason');
            $table->string('visit_charge_method')->nullable()->after('visit_charge_amount'); // cash|bank_transfer
            $table->string('visit_charge_screenshot_path')->nullable()->after('visit_charge_method');
            $table->timestamp('visit_charge_collected_at')->nullable()->after('visit_charge_screenshot_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['visit_charge_amount', 'visit_charge_method', 'visit_charge_screenshot_path', 'visit_charge_collected_at']);
        });
    }
};
