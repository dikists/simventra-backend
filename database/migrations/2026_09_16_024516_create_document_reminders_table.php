<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_reminders', function (Blueprint $table) {
            $table->id();
            $table->morphs('documentable'); // vehicle_documents atau employee_documents
            $table->string('document_type');
            $table->string('document_title');
            $table->string('related_name');        // Nama kendaraan/karyawan
            $table->date('expiry_date');
            $table->integer('days_before');        // 30, 14, atau 7
            $table->enum('channel', ['email', 'whatsapp', 'system'])->default('email');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['status', 'days_before']);
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_reminders');
    }
};
