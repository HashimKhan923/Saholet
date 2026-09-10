<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('consumer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();

            $table->string('fulfillment_method'); // delivery | pickup

            // Shipping snapshot — set for delivery orders only, denormalized the
            // same way bookings snapshot their own address/latitude/longitude.
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();
            $table->text('shipping_address')->nullable();
            $table->string('shipping_city')->nullable();
            $table->decimal('shipping_lat', 10, 7)->nullable();
            $table->decimal('shipping_lng', 10, 7)->nullable();

            $table->decimal('subtotal', 12, 2);
            $table->decimal('shipping_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);

            $table->string('payment_method'); // cash | bank_transfer
            $table->string('status')->default('pending'); // pending | confirmed | ready | completed | cancelled

            // Filled in by the provider when marking a delivery order "ready".
            $table->string('delivery_method')->nullable();
            $table->string('tracking_reference')->nullable();

            $table->decimal('commission_rate', 5, 2)->nullable();
            $table->decimal('commission_amount', 12, 2)->nullable();
            $table->decimal('provider_amount', 12, 2)->nullable();

            $table->string('cancel_reason')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index(['consumer_id', 'status']);
            $table->index(['provider_profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
