<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['assigned', 'confirmed', 'on_trip', 'completed', 'cancelled'])->default('assigned');
            $table->string('origin')->default('Gudang Utama');
            $table->string('destination')->nullable();
            $table->decimal('start_odometer', 10, 2)->default(0);
            $table->decimal('end_odometer', 10, 2)->nullable();
            $table->timestamp('departure_time')->nullable();
            $table->timestamp('return_time')->nullable();
            $table->text('notes')->nullable();
            $table->string('vehicle_condition_on_return')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vehicle_id', 'status']);
            $table->index(['driver_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_assignments');
    }
};
