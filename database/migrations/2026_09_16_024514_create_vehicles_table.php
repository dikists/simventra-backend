<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_code')->unique();       // Kode internal kendaraan
            $table->string('license_plate')->unique();      // No. polisi
            $table->string('brand');                        // Merk
            $table->string('model')->nullable();            // Tipe/Model
            $table->string('vehicle_type');                 // Truk, Pickup, Van, Motor, dll
            $table->integer('year')->nullable();
            $table->string('color')->nullable();
            $table->string('chassis_number')->nullable();   // No. rangka
            $table->string('engine_number')->nullable();    // No. mesin
            $table->decimal('max_capacity_kg', 10, 2)->nullable(); // Kapasitas muatan
            $table->decimal('current_odometer_km', 10, 2)->nullable();
            $table->enum('fuel_type', ['solar', 'bensin', 'listrik', 'hybrid'])->default('solar');
            $table->boolean('is_halal_dedicated')->default(false); // Kendaraan khusus halal
            $table->enum('status', ['active', 'maintenance', 'inactive', 'scrapped'])->default('active');
            $table->foreignId('assigned_driver_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('gps_device_id')->nullable();    // ID tracker GPS
            $table->text('notes')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('is_halal_dedicated');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
