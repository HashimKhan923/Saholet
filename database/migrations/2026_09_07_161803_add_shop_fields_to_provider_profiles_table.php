<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->string('shop_name')->nullable()->after('business_name');

            // Delivery: unset means the provider doesn't ship at all.
            $table->string('shipping_type')->nullable()->after('commission_rate'); // flat | area | percentage
            $table->decimal('shipping_flat_rate', 10, 2)->nullable()->after('shipping_type');
            $table->decimal('shipping_percentage', 5, 2)->nullable()->after('shipping_flat_rate');

            // Self-pickup: location reuses this profile's own address/latitude/longitude.
            $table->boolean('pickup_enabled')->default(false)->after('shipping_percentage');

            // Commission on product sales, separate from the booking commission_rate above.
            $table->decimal('product_commission_rate', 5, 2)->nullable()->after('pickup_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'shop_name',
                'shipping_type',
                'shipping_flat_rate',
                'shipping_percentage',
                'pickup_enabled',
                'product_commission_rate',
            ]);
        });
    }
};
