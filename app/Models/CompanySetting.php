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
        'heartbeat_timeout_minutes',
        'heartbeat_cooldown_minutes',
        'wa_alert_enabled',
        'wa_notify_driver',
        'wa_notify_dispatcher',
        'control_tower_phone',
    ];

    protected $casts = [
        'wa_alert_enabled'           => 'boolean',
        'wa_notify_driver'          => 'boolean',
        'wa_notify_dispatcher'      => 'boolean',
        'heartbeat_timeout_minutes' => 'integer',
        'heartbeat_cooldown_minutes'=> 'integer',
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
                'name'                       => 'PT Rajawali Handal Logistik',
                'short_name'                 => 'Rajawali Handal',
                'tagline'                    => 'Logistik',
                'logo_path'                  => '/assets/logo_rhl.png',
                'heartbeat_timeout_minutes'  => 5,
                'heartbeat_cooldown_minutes' => 10,
                'wa_alert_enabled'           => true,
                'wa_notify_driver'          => true,
                'wa_notify_dispatcher'      => true,
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

    public static function getHeartbeatTimeout(): int
    {
        $val = static::getSettings()->heartbeat_timeout_minutes;
        return ($val !== null && $val > 0) ? (int) $val : (int) config('simventra.heartbeat.timeout_minutes', 5);
    }

    public static function getHeartbeatCooldown(): int
    {
        $val = static::getSettings()->heartbeat_cooldown_minutes;
        return ($val !== null && $val > 0) ? (int) $val : (int) config('simventra.heartbeat.cooldown_minutes', 10);
    }

    public static function isWaAlertEnabled(): bool
    {
        $val = static::getSettings()->wa_alert_enabled;
        return $val !== null ? (bool) $val : true;
    }

    public static function shouldNotifyDriver(): bool
    {
        $val = static::getSettings()->wa_notify_driver;
        return $val !== null ? (bool) $val : true;
    }

    public static function shouldNotifyDispatcher(): bool
    {
        $val = static::getSettings()->wa_notify_dispatcher;
        return $val !== null ? (bool) $val : true;
    }

    public static function getControlTowerPhone(): ?string
    {
        $phone = static::getSettings()->control_tower_phone;
        return !empty($phone) ? $phone : config('simventra.notifications.admin_phone');
    }

    /**
     * Bersihkan cache saat ada pembaruan data.
     */
    public static function clearCache(): void
    {
        Cache::forget('company_settings');
    }
}
