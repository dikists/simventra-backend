<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix: ubah silence_minutes dari unsignedSmallInteger ke smallInteger
 * untuk menghindari error "Numeric value out of range" jika ada edge case negatif.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('heartbeat_alerts', function (Blueprint $table) {
            // Ganti dari unsignedSmallInteger ke smallInteger (allow signed)
            $table->smallInteger('silence_minutes')->change();
        });
    }

    public function down(): void
    {
        Schema::table('heartbeat_alerts', function (Blueprint $table) {
            $table->unsignedSmallInteger('silence_minutes')->change();
        });
    }
};
