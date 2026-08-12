<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->decimal('commission_rate', 5, 2)->nullable()->after('reviewed_by');
        });

        // Commission is moving from a single platform-wide setting to a rate set
        // per provider at approval time. Backfill every existing provider with
        // today's effective global rate so nothing changes for them the moment
        // the global setting is retired — going forward, admins set this
        // explicitly per provider instead.
        $globalRate = (float) (DB::table('settings')->where('key', 'commission_rate')->value('value') ?? 10.0);

        DB::table('provider_profiles')->update(['commission_rate' => $globalRate]);
    }

    public function down(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropColumn('commission_rate');
        });
    }
};
