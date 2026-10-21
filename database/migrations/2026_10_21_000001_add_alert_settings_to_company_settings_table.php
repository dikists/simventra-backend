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
        Schema::table('company_settings', function (Blueprint $table) {
            $table->unsignedInteger('heartbeat_timeout_minutes')->default(5)->after('address');
            $table->unsignedInteger('heartbeat_cooldown_minutes')->default(10)->after('heartbeat_timeout_minutes');
            $table->boolean('wa_alert_enabled')->default(true)->after('heartbeat_cooldown_minutes');
            $table->boolean('wa_notify_driver')->default(true)->after('wa_alert_enabled');
            $table->boolean('wa_notify_dispatcher')->default(true)->after('wa_notify_driver');
            $table->string('control_tower_phone', 30)->nullable()->after('wa_notify_dispatcher');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'heartbeat_timeout_minutes',
                'heartbeat_cooldown_minutes',
                'wa_alert_enabled',
                'wa_notify_driver',
                'wa_notify_dispatcher',
                'control_tower_phone',
            ]);
        });
    }
};
