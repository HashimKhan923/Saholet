<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emergency_requests', function (Blueprint $table) {
            $table->decimal('quoted_price', 10, 2)->nullable()->after('notes');
            $table->timestamp('quoted_at')->nullable()->after('quoted_price');
            $table->foreignId('quoted_by')->nullable()->constrained('users')->nullOnDelete()->after('quoted_at');
            $table->text('admin_note')->nullable()->after('quoted_by');
            $table->timestamp('accepted_at')->nullable()->after('matched_at');
            $table->timestamp('declined_at')->nullable()->after('accepted_at');
        });
    }

    public function down(): void
    {
        Schema::table('emergency_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quoted_by');
            $table->dropColumn(['quoted_price', 'quoted_at', 'admin_note', 'accepted_at', 'declined_at']);
        });
    }
};
