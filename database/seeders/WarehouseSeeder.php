<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        Warehouse::firstOrCreate(
            ['is_primary' => true],
            [
                'name'      => env('WAREHOUSE_NAME', 'Rajawali Handal Logistik'),
                'address'   => env('WAREHOUSE_ADDRESS', 'Jl. Raya Logistik No. 1, Jakarta'),
                'latitude'  => env('WAREHOUSE_LAT', -6.2088),
                'longitude' => env('WAREHOUSE_LNG', 106.8456),
                'is_active' => true,
            ]
        );
    }
}
