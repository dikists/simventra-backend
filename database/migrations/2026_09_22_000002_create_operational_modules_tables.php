<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Jadwal & Log Maintenance Kendaraan
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('maintenance_type');              // oli_mesin, ban, rem, ac, servis_berkala, dll
            $table->string('trigger_type')->default('km');   // km, waktu, manual
            $table->decimal('trigger_km', 10, 2)->nullable();     // Interval KM untuk servis
            $table->integer('trigger_days')->nullable();          // Interval hari untuk servis
            $table->decimal('last_done_km', 10, 2)->nullable();   // KM saat terakhir servis
            $table->date('last_done_date')->nullable();
            $table->decimal('next_due_km', 10, 2)->nullable();    // KM berikutnya servis
            $table->date('next_due_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained('maintenance_schedules')->nullOnDelete();
            $table->date('maintenance_date');
            $table->string('maintenance_type');               // oli_mesin, ban, rem, dll
            $table->text('description')->nullable();          // Deskripsi pekerjaan
            $table->string('workshop')->nullable();           // Nama bengkel
            $table->string('mechanic')->nullable();           // Nama mekanik
            $table->decimal('cost', 12, 2)->default(0);       // Biaya perbaikan
            $table->decimal('odometer_km', 10, 2)->nullable(); // Odometer saat servis
            $table->string('status')->default('completed');    // completed, pending, cancelled
            $table->text('parts_replaced')->nullable();        // Part yang diganti
            $table->string('receipt_photo')->nullable();       // Foto kwitansi
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ── Insiden (Keamanan, Kontaminasi, Kecelakaan, Pelanggaran)
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('incident_number')->unique();     // Auto-generated: INC-2026-0001
            $table->string('type');                          // keamanan, kontaminasi, kecelakaan, pelanggaran, lainnya
            $table->string('severity')->default('medium');   // low, medium, high, critical
            $table->string('title');
            $table->text('description');
            $table->dateTime('occurred_at');
            $table->string('location')->nullable();          // Lokasi kejadian
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assignment_id')->nullable()->constrained('vehicle_assignments')->nullOnDelete();
            $table->string('status')->default('open');       // open, investigating, resolved, closed
            $table->text('resolution')->nullable();          // Deskripsi penyelesaian
            $table->dateTime('resolved_at')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('incident_followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('action_taken');
            $table->string('status_change')->nullable();     // Status baru setelah tindakan
            $table->string('attachment')->nullable();         // Bukti foto/dokumen
            $table->timestamps();
        });

        // ── Komplain Pelanggan
        Schema::create('customer_complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_number')->unique();    // Auto-generated: CMP-2026-0001
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('subject');
            $table->text('description');
            $table->dateTime('received_at');
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('assigned_pic')->nullable()->constrained('users')->nullOnDelete(); // PIC penanganan
            $table->string('status')->default('open');       // open, in_progress, resolved, closed
            $table->string('priority')->default('normal');   // low, normal, high, urgent
            $table->text('resolution')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('complaint_followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained('customer_complaints')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('action_taken');
            $table->string('status_change')->nullable();
            $table->timestamps();
        });

        // ── Management Review (QMS)
        Schema::create('management_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('review_date');
            $table->string('review_type')->default('berkala'); // berkala, tahunan, khusus
            $table->text('agenda')->nullable();
            $table->text('minutes')->nullable();              // Notulen rapat
            $table->text('conclusions')->nullable();
            $table->string('status')->default('scheduled');   // scheduled, completed
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('review_action_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('management_reviews')->cascadeOnDelete();
            $table->string('action');                         // Deskripsi tindak lanjut
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->string('status')->default('open');        // open, in_progress, completed, overdue
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_action_items');
        Schema::dropIfExists('management_reviews');
        Schema::dropIfExists('complaint_followups');
        Schema::dropIfExists('customer_complaints');
        Schema::dropIfExists('incident_followups');
        Schema::dropIfExists('incidents');
        Schema::dropIfExists('maintenance_logs');
        Schema::dropIfExists('maintenance_schedules');
    }
};
