<?php

use App\Livewire\Dashboard;
use App\Livewire\Employees\EmployeeIndex;
use App\Livewire\Employees\EmployeeForm;
use App\Livewire\Employees\EmployeeDetail;
use App\Livewire\Vehicles\VehicleIndex;
use App\Livewire\Vehicles\VehicleForm;
use App\Livewire\Vehicles\VehicleDetail;
use App\Livewire\Documents\EmployeeDocumentIndex;
use App\Livewire\Documents\VehicleDocumentIndex;
use App\Livewire\Reminders\ReminderIndex;
use App\Livewire\Reports\ReportIndex;
use App\Livewire\Users\UserIndex;
use App\Livewire\ActivityLogs\ActivityLogIndex;
use App\Livewire\Monitoring\LiveTrackingMap;
use App\Livewire\Settings\WarehouseSettings;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard
Route::redirect('/', '/dashboard');

// Public Driver App (React Mobile PWA)
Route::view('/sopir', 'driver-app')->name('driver.app');

// Auth routes (Breeze) – MUST be before protected routes so logout, login etc. are registered
require __DIR__.'/auth.php';

// ==========================================
// PROTECTED ROUTES (auth required)
// ==========================================
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Profile – Breeze registers this; just add an alias for our sidebar links
    Route::view('profile', 'profile')->name('profile.edit');

    // ===== KARYAWAN =====
    Route::middleware('can:create employees')->group(function () {
        Route::get('/karyawan/tambah', EmployeeForm::class)->name('employees.create');
    });
    Route::middleware('can:edit employees')->group(function () {
        Route::get('/karyawan/{employee}/edit', EmployeeForm::class)->whereNumber('employee')->name('employees.edit');
    });
    Route::middleware('can:view employees')->group(function () {
        Route::get('/karyawan', EmployeeIndex::class)->name('employees.index');
        Route::get('/karyawan/{employee}', EmployeeDetail::class)->whereNumber('employee')->name('employees.show');
    });

    // ===== KENDARAAN =====
    Route::middleware('can:create vehicles')->group(function () {
        Route::get('/kendaraan/tambah', VehicleForm::class)->name('vehicles.create');
    });
    Route::middleware('can:edit vehicles')->group(function () {
        Route::get('/kendaraan/{vehicle}/edit', VehicleForm::class)->whereNumber('vehicle')->name('vehicles.edit');
    });
    Route::middleware('can:view vehicles')->group(function () {
        Route::get('/kendaraan', VehicleIndex::class)->name('vehicles.index');
        Route::get('/kendaraan/{vehicle}', VehicleDetail::class)->whereNumber('vehicle')->name('vehicles.show');
        Route::get('/monitoring-armada', LiveTrackingMap::class)->name('tracking.index');
    });

    // ===== DOKUMEN =====
    Route::middleware('can:view employee documents')->group(function () {
        Route::get('/dokumen/karyawan', EmployeeDocumentIndex::class)->name('documents.employees');
    });
    Route::middleware('can:view vehicle documents')->group(function () {
        Route::get('/dokumen/kendaraan', VehicleDocumentIndex::class)->name('documents.vehicles');
    });

    // ===== REMINDER =====
    Route::middleware('can:view reminders')->group(function () {
        Route::get('/reminder', ReminderIndex::class)->name('reminders.index');
    });

    // ===== LAPORAN =====
    Route::middleware('can:view reports')->group(function () {
        Route::get('/laporan', ReportIndex::class)->name('reports.index');
    });

    // ===== USERS (Admin) =====
    Route::middleware('can:view users')->group(function () {
        Route::get('/pengguna', UserIndex::class)->name('users.index');
    });

    // ===== ACTIVITY LOG =====
    Route::middleware('can:view activity logs')->group(function () {
        Route::get('/log-aktivitas', ActivityLogIndex::class)->name('activity-logs.index');
    });

    // ===== PENGATURAN GUDANG =====
    Route::middleware('can:view users')->group(function () {
        Route::get('/pengaturan/gudang', WarehouseSettings::class)->name('settings.warehouse');
    });
});
