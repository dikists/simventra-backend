<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pusat Distribusi / Gudang Utama
    |--------------------------------------------------------------------------
    | Konfigurasi alamat dan koordinat pusat distribusi / gudang utama.
    | Sesuaikan nilai di file .env untuk mengubah data gudang.
    |
    */

    'name'    => env('WAREHOUSE_NAME', 'Pusat Distribusi Utama'),
    'address' => env('WAREHOUSE_ADDRESS', 'Jakarta, Indonesia'),
    'lat'     => (float) env('WAREHOUSE_LAT', -6.2088),
    'lng'     => (float) env('WAREHOUSE_LNG', 106.8456),

];
