<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// SIMVENTRA – Scheduler Harian
// Jalankan cek dokumen kadaluarsa setiap hari pukul 07:00 WIB
Schedule::command('simventra:check-document-expiry')
    ->dailyAt('07:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->runInBackground()
    ->emailOutputOnFailure(env('ADMIN_EMAIL', 'admin@simventra.id'));

// SIMVENTRA – Auto-Recall Penugasan Armada
// Deringkan ulang HP sopir setiap menit jika ada tugas yang belum dikonfirmasi > 2 menit
Schedule::command('simventra:recall-assignments')
    ->everyMinute()
    ->withoutOverlapping();

// SIMVENTRA – Heartbeat Checker (Driver GPS Monitor)
// Deteksi sopir yang menutup aplikasi saat perjalanan setiap 2 menit.
// Jika tidak ada sinyal GPS selama HEARTBEAT_TIMEOUT_MINUTES, kirim alert
// via Push Notification + WhatsApp ke sopir dan Dispatcher.
Schedule::command('simventra:check-driver-heartbeat')
    ->everyTwoMinutes()
    ->withoutOverlapping()
    ->runInBackground();
