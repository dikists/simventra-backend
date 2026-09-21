<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('heartbeat_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('vehicle_assignments')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();

            // Kapan terakhir ping lokasi diterima saat alert ini dibuat
            $table->timestamp('last_ping_at')->nullable();

            // Berapa menit sopir tidak mengirim lokasi
            $table->unsignedSmallInteger('silence_minutes');

            // Status pengiriman notifikasi
            $table->boolean('push_sent_driver')->default(false);
            $table->boolean('push_sent_dispatcher')->default(false);
            $table->boolean('wa_sent')->default(false);

            // Catatan error jika gagal kirim
            $table->text('error_notes')->nullable();

            $table->timestamps();

            $table->index(['assignment_id', 'created_at']);
            $table->index(['driver_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heartbeat_alerts');
    }
};
