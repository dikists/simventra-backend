<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_number')->unique(); // NIP/ID Karyawan
            $table->string('name');
            $table->enum('type', ['karyawan', 'sopir', 'mitra'])->default('karyawan');
            $table->enum('employment_status', ['tetap', 'kontrak', 'magang', 'mitra'])->default('tetap');
            $table->string('position')->nullable();         // Jabatan
            $table->string('department')->nullable();       // Departemen
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->string('nik')->nullable()->unique();    // No. KTP
            $table->string('npwp')->nullable();
            $table->date('join_date')->nullable();
            $table->date('contract_end_date')->nullable();
            // Sopir-specific fields
            $table->string('sim_number')->nullable();       // No. SIM
            $table->string('sim_type')->nullable();         // A, B1, B2, BI
            $table->date('sim_expiry')->nullable();
            $table->string('license_plate_assigned')->nullable(); // Plat kendaraan yang ditugaskan
            // Background check
            $table->boolean('has_skck')->default(false);
            $table->date('skck_expiry')->nullable();
            $table->text('criminal_record_notes')->nullable(); // Enkripsi di level app
            // ID Card
            $table->string('id_card_number')->nullable();
            $table->date('id_card_expiry')->nullable();
            $table->boolean('id_card_active')->default(true);
            // Status
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
            $table->text('notes')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'status']);
            $table->index('sim_expiry');
        });

        // Tabel kontak kerabat
        Schema::create('employee_relatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('relationship'); // ayah, ibu, istri, suami, anak, dll
            $table->string('phone');
            $table->string('address')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_relatives');
        Schema::dropIfExists('employees');
    }
};
