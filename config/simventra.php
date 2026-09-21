<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Simventra – Heartbeat Checker (Driver GPS Monitoring)
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk deteksi sopir yang menutup aplikasi saat perjalanan.
    |
    | timeout_minutes  : Berapa menit tanpa sinyal GPS sebelum alert dikirim.
    | cooldown_minutes : Jeda minimum antar alert untuk assignment yang sama
    |                    (mencegah spam notifikasi).
    |
    */
    'heartbeat' => [
        'timeout_minutes'  => (int) env('HEARTBEAT_TIMEOUT_MINUTES', 5),
        'cooldown_minutes' => (int) env('HEARTBEAT_COOLDOWN_MINUTES', 10),
    ],

];
