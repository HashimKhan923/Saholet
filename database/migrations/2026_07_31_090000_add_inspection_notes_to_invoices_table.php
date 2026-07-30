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
        Schema::table('invoices', function (Blueprint $table) {
            // What was inspected/found on-site, shown between customer details and
            // line items on the document — entirely optional, no heading printed
            // at all when left blank.
            $table->text('inspection_notes')->nullable()->after('bill_to_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('inspection_notes');
        });
    }
};
