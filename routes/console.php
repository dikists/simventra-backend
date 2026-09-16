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
