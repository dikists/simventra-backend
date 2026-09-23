<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah indeks pada kolom-kolom yang sering di-query untuk optimasi performa.
     */
    public function up(): void
    {
        // ── vehicle_assignments: status, driver_id, vehicle_id sangat sering di-filter
        Schema::table('vehicle_assignments', function (Blueprint $table) {
            $table->index('status', 'idx_va_status');
            $table->index('driver_id', 'idx_va_driver_id');
            $table->index('vehicle_id', 'idx_va_vehicle_id');
            $table->index(['status', 'driver_id'], 'idx_va_status_driver');
            $table->index('last_ping_at', 'idx_va_last_ping_at');
        });

        // ── driver_locations: query by assignment dan waktu
        Schema::table('driver_locations', function (Blueprint $table) {
            $table->index('assignment_id', 'idx_dl_assignment_id');
            $table->index('recorded_at', 'idx_dl_recorded_at');
            $table->index(['assignment_id', 'recorded_at'], 'idx_dl_assignment_recorded');
        });

        // ── employee_documents: expiry_date untuk reminder checker
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->index('expiry_date', 'idx_ed_expiry_date');
            $table->index('status', 'idx_ed_status');
        });

        // ── vehicle_documents: expiry_date untuk reminder checker
        Schema::table('vehicle_documents', function (Blueprint $table) {
            $table->index('expiry_date', 'idx_vd_expiry_date');
            $table->index('status', 'idx_vd_status');
        });

        // ── employees: type dan status untuk filter sopir aktif
        Schema::table('employees', function (Blueprint $table) {
            $table->index('type', 'idx_emp_type');
            $table->index('status', 'idx_emp_status');
            $table->index('user_id', 'idx_emp_user_id');
            $table->index(['type', 'status'], 'idx_emp_type_status');
        });

        // ── vehicles: status untuk filter armada aktif
        Schema::table('vehicles', function (Blueprint $table) {
            $table->index('status', 'idx_veh_status');
            $table->index('assigned_driver_id', 'idx_veh_driver');
        });

        // ── heartbeat_alerts: query by assignment
        Schema::table('heartbeat_alerts', function (Blueprint $table) {
            $table->index('assignment_id', 'idx_ha_assignment_id');
            $table->index('created_at', 'idx_ha_created_at');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_assignments', function (Blueprint $table) {
            $table->dropIndex('idx_va_status');
            $table->dropIndex('idx_va_driver_id');
            $table->dropIndex('idx_va_vehicle_id');
            $table->dropIndex('idx_va_status_driver');
            $table->dropIndex('idx_va_last_ping_at');
        });

        Schema::table('driver_locations', function (Blueprint $table) {
            $table->dropIndex('idx_dl_assignment_id');
            $table->dropIndex('idx_dl_recorded_at');
            $table->dropIndex('idx_dl_assignment_recorded');
        });

        Schema::table('employee_documents', function (Blueprint $table) {
            $table->dropIndex('idx_ed_expiry_date');
            $table->dropIndex('idx_ed_status');
        });

        Schema::table('vehicle_documents', function (Blueprint $table) {
            $table->dropIndex('idx_vd_expiry_date');
            $table->dropIndex('idx_vd_status');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('idx_emp_type');
            $table->dropIndex('idx_emp_status');
            $table->dropIndex('idx_emp_user_id');
            $table->dropIndex('idx_emp_type_status');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex('idx_veh_status');
            $table->dropIndex('idx_veh_driver');
        });

        Schema::table('heartbeat_alerts', function (Blueprint $table) {
            $table->dropIndex('idx_ha_assignment_id');
            $table->dropIndex('idx_ha_created_at');
        });
    }
};
