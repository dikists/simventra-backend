<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Heartbeat / GPS Monitoring
    |--------------------------------------------------------------------------
    | Konfigurasi untuk deteksi sopir yang tidak mengirim sinyal GPS
    | saat status on_trip (heartbeat checker).
    */
    'heartbeat' => [
        'timeout_minutes'  => (int) env('HEARTBEAT_TIMEOUT_MINUTES', 5),
        'cooldown_minutes' => (int) env('HEARTBEAT_COOLDOWN_MINUTES', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | GPS Location Tracking
    |--------------------------------------------------------------------------
    | Pengaturan terkait pencatatan lokasi GPS sopir.
    */
    'gps' => [
        // Interval minimum antar batch upload dari mobile (detik)
        'batch_interval_seconds' => (int) env('GPS_BATCH_INTERVAL_SECONDS', 15),

        // Maksimum titik GPS yang diterima per batch request
        'max_points_per_batch' => (int) env('GPS_MAX_POINTS_PER_BATCH', 20),

        // Berapa hari data GPS disimpan sebelum dipangkas (prune)
        'retention_days' => (int) env('GPS_RETENTION_DAYS', 30),

        // Jumlah titik trail terakhir yang dikirim ke peta monitoring
        'trail_points' => (int) env('GPS_TRAIL_POINTS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Speed Limits (KM/H)
    |--------------------------------------------------------------------------
    | Batas kecepatan yang dimonitor oleh Control Tower.
    */
    'speed_limits' => [
        'highway'  => (int) env('SPEED_LIMIT_HIGHWAY', 80),
        'arterial' => (int) env('SPEED_LIMIT_ARTERIAL', 50),
        'alert_threshold' => (int) env('SPEED_ALERT_THRESHOLD', 85),
    ],

    /*
    |--------------------------------------------------------------------------
    | Document Expiry Reminder
    |--------------------------------------------------------------------------
    | Konfigurasi reminder otomatis dokumen yang akan kadaluarsa.
    */
    'document_reminder' => [
        // Hari sebelum kadaluarsa untuk mengirim reminder
        'days_before' => array_map('intval', explode(',', env('DOC_REMINDER_DAYS_BEFORE', '30,14,7'))),

        // Jam pengiriman reminder (WIB)
        'send_at_hour' => env('DOC_REMINDER_HOUR', '07:00'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cron / Scheduler
    |--------------------------------------------------------------------------
    */
    'cron' => [
        'secret' => env('CRON_SECRET', 'simventra_cron_rahasia_2026'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Recall / Auto-Reminder Penugasan
    |--------------------------------------------------------------------------
    | Konfigurasi untuk dering ulang HP sopir jika tugas belum dikonfirmasi.
    */
    'recall' => [
        // Berapa menit belum konfirmasi sebelum recall dipicu
        'after_minutes' => (int) env('RECALL_AFTER_MINUTES', 2),

        // Batas maksimal recall agar tidak spam
        'max_retries' => (int) env('RECALL_MAX_RETRIES', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Channels
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        // Nomor HP admin/control tower untuk alert kritis
        'admin_phone' => env('CONTROL_TOWER_PHONE', env('ADMIN_PHONE')),
        'admin_email' => env('ADMIN_EMAIL', 'admin@simventra.id'),

        // Role yang menerima notifikasi dispatcher
        'dispatcher_roles' => ['Super Admin', 'Fleet Officer', 'Control Tower Officer'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Warehouse / Gudang Default
    |--------------------------------------------------------------------------
    */
    'warehouse' => [
        'name'    => env('WAREHOUSE_NAME', 'Gudang Utama'),
        'address' => env('WAREHOUSE_ADDRESS'),
        'lat'     => (float) env('WAREHOUSE_LAT', -6.2088),
        'lng'     => (float) env('WAREHOUSE_LNG', 106.8456),
    ],

];
