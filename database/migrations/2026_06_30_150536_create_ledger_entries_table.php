<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('bucket');          // escrow | available
            $table->string('type');            // hold | release_in | release_out | refund_in | refund_out
            $table->decimal('amount', 12, 2);  // signed: + credit, - debit (within the bucket)
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['wallet_id', 'bucket']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};