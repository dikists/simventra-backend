<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CompanySetting extends Model
{
    protected $fillable = [
        'name',
        'short_name',
        'tagline',
        'logo_path',
        'phone',
        'email',
        'address',
    ];

    /**
     * Ambil pengaturan perusahaan singleton (aman dari serialization error).
     */
    public static function getSettings(): self
    {
        $cached = Cache::get('company_settings');

        if ($cached instanceof self) {
            return $cached;
        }

        Cache::forget('company_settings');

        $setting = static::first();

        if (! $setting) {
            $setting = static::create([
                'name'       => 'PT Rajawali Handal Logistik',
                'short_name' => 'Rajawali Handal',
                'tagline'    => 'Logistik',
                'logo_path'  => '/assets/logo_rhl.png',
            ]);
        }

        Cache::forever('company_settings', $setting);

        return $setting;
    }

    /**
     * URL logo perusahaan yang valid sesuai default_public_disk.
     */
    public static function getLogoUrl(): string
    {
        $setting = static::getSettings();
        if (! empty($setting->logo_path)) {
            if (str_starts_with($setting->logo_path, '/assets') || str_starts_with($setting->logo_path, 'http')) {
                return $setting->logo_path;
            }
            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk(config('filesystems.default_public_disk'));
            return $disk->url($setting->logo_path);
        }
        return asset('assets/logo_rhl.png');
    }

    public static function getName(): string
    {
        return static::getSettings()->name ?? 'PT Rajawali Handal Logistik';
    }

    public static function getShortName(): string
    {
        return static::getSettings()->short_name ?? 'Rajawali Handal';
    }

    public static function getTagline(): string
    {
        return static::getSettings()->tagline ?? 'Logistik';
    }

    /**
     * Bersihkan cache saat ada pembaruan data.
     */
    public static function clearCache(): void
    {
        Cache::forget('company_settings');
    }
}
