<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_assignments', function (Blueprint $table) {
            // Timestamp terakhir lokasi GPS diterima dari sopir
            $table->timestamp('last_ping_at')->nullable()->after('return_time');

            // Timestamp terakhir alert heartbeat dikirim (untuk anti-spam cooldown)
            $table->timestamp('heartbeat_alerted_at')->nullable()->after('last_ping_at');

            $table->index('last_ping_at');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_assignments', function (Blueprint $table) {
            $table->dropIndex(['last_ping_at']);
            $table->dropColumn(['last_ping_at', 'heartbeat_alerted_at']);
        });
    }
};
