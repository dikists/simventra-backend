<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Warehouse extends Model
{
    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'is_primary',
        'is_active',
    ];

    protected $casts = [
        'latitude'   => 'float',
        'longitude'  => 'float',
        'is_primary' => 'boolean',
        'is_active'  => 'boolean',
    ];

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true)->where('is_active', true);
    }

    // -------------------------------------------------------
    // Static Helpers
    // -------------------------------------------------------

    /**
     * Ambil gudang utama.
     * Fallback ke env/config jika DB belum ada data.
     */
    public static function getPrimary(): self
    {
        $warehouse = static::primary()->first();

        if (! $warehouse) {
            // Fallback ke config/.env jika belum ada data
            $warehouse = new static([
                'name'      => config('warehouse.name', 'Pusat Distribusi Utama'),
                'address'   => config('warehouse.address', 'Jakarta, Indonesia'),
                'latitude'  => config('warehouse.lat', -6.2088),
                'longitude' => config('warehouse.lng', 106.8456),
                'is_primary'=> true,
                'is_active' => true,
            ]);
        }

        return $warehouse;
    }

    /**
     * Hapus cache gudang utama jika ada.
     */
    public static function clearPrimaryCache(): void
    {
        Cache::forget('warehouse_primary');
    }
}
