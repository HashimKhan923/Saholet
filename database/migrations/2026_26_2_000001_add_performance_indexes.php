<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->index('consumer_id', 'bookings_consumer_id_index');
            $table->index('status', 'bookings_status_index');
        });

        Schema::table('job_posts', function (Blueprint $table) {
            $table->index('consumer_id', 'job_posts_consumer_id_index');
        });

        Schema::table('bids', function (Blueprint $table) {
            $table->index('provider_profile_id', 'bids_provider_profile_id_index');
            $table->index('status', 'bids_status_index');
        });

        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->index('status', 'provider_profiles_status_index');
            $table->index('city', 'provider_profiles_city_index');
        });

        Schema::table('provider_services', function (Blueprint $table) {
            $table->index('service_id', 'provider_services_service_id_index');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index('status', 'payments_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_consumer_id_index');
            $table->dropIndex('bookings_status_index');
        });
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropIndex('job_posts_consumer_id_index');
        });
        Schema::table('bids', function (Blueprint $table) {
            $table->dropIndex('bids_provider_profile_id_index');
            $table->dropIndex('bids_status_index');
        });
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropIndex('provider_profiles_status_index');
            $table->dropIndex('provider_profiles_city_index');
        });
        Schema::table('provider_services', function (Blueprint $table) {
            $table->dropIndex('provider_services_service_id_index');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_status_index');
        });
    }
};