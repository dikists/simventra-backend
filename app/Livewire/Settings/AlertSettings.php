<?php

namespace App\Livewire\Settings;

use App\Models\CompanySetting;
use App\Services\WhatsAppService;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Pengaturan Alert GPS & Notifikasi WhatsApp')]
class AlertSettings extends Component
{
    public int $heartbeat_timeout_minutes = 5;
    public int $heartbeat_cooldown_minutes = 10;
    public bool $wa_alert_enabled = true;
    public bool $wa_notify_driver = true;
    public bool $wa_notify_dispatcher = true;
    public string|null $control_tower_phone = '';

    // Testing WhatsApp state
    public string $test_phone = '';
    public string|null $test_result_type = null; // 'success' | 'error'
    public string|null $test_result_message = null;

    public bool $saved = false;

    public function mount(): void
    {
        $setting = CompanySetting::getSettings();

        $this->heartbeat_timeout_minutes  = (int) ($setting->heartbeat_timeout_minutes ?? config('simventra.heartbeat.timeout_minutes', 5));
        $this->heartbeat_cooldown_minutes = (int) ($setting->heartbeat_cooldown_minutes ?? config('simventra.heartbeat.cooldown_minutes', 10));
        $this->wa_alert_enabled           = $setting->wa_alert_enabled !== null ? (bool) $setting->wa_alert_enabled : true;
        $this->wa_notify_driver           = $setting->wa_notify_driver !== null ? (bool) $setting->wa_notify_driver : true;
        $this->wa_notify_dispatcher       = $setting->wa_notify_dispatcher !== null ? (bool) $setting->wa_notify_dispatcher : true;
        $this->control_tower_phone        = $setting->control_tower_phone ?? config('simventra.notifications.admin_phone', '');

        // Prefill test phone with control tower phone or user's phone if available
        $this->test_phone = $this->control_tower_phone ?: (auth()->user()?->phone ?? '');
    }

    public function setCooldown(int $minutes): void
    {
        $this->heartbeat_cooldown_minutes = $minutes;
    }

    public function setTimeout(int $minutes): void
    {
        $this->heartbeat_timeout_minutes = $minutes;
    }

    public function save(): void
    {
        $this->validate([
            'heartbeat_timeout_minutes'  => 'required|integer|min:1|max:120',
            'heartbeat_cooldown_minutes' => 'required|integer|min:1|max:1440',
            'wa_alert_enabled'           => 'boolean',
            'wa_notify_driver'           => 'boolean',
            'wa_notify_dispatcher'       => 'boolean',
            'control_tower_phone'        => 'nullable|string|max:30',
        ], [
            'heartbeat_timeout_minutes.required'  => 'Batas waktu toleransi sinyal harus diisi.',
            'heartbeat_timeout_minutes.min'       => 'Batas waktu toleransi minimal 1 menit.',
            'heartbeat_timeout_minutes.max'       => 'Batas waktu toleransi maksimal 120 menit (2 jam).',
            'heartbeat_cooldown_minutes.required' => 'Interval jeda alert harus diisi.',
            'heartbeat_cooldown_minutes.min'      => 'Interval jeda minimal 1 menit.',
            'heartbeat_cooldown_minutes.max'      => 'Interval jeda maksimal 1440 menit (24 jam).',
        ]);

        $setting = CompanySetting::first();
        if (! $setting) {
            $setting = new CompanySetting();
        }

        $setting->heartbeat_timeout_minutes  = $this->heartbeat_timeout_minutes;
        $setting->heartbeat_cooldown_minutes = $this->heartbeat_cooldown_minutes;
        $setting->wa_alert_enabled           = $this->wa_alert_enabled;
        $setting->wa_notify_driver           = $this->wa_notify_driver;
        $setting->wa_notify_dispatcher       = $this->wa_notify_dispatcher;
        $setting->control_tower_phone        = !empty($this->control_tower_phone) ? trim($this->control_tower_phone) : null;

        $setting->save();

        CompanySetting::clearCache();

        $this->saved = true;

        session()->flash('success', "Pengaturan berhasil disimpan! Alert WA akan dikirim dengan jeda {$this->heartbeat_cooldown_minutes} menit sekali jika sopir tidak update GPS.");
    }

    public function sendTestWhatsApp(): void
    {
        $this->reset(['test_result_type', 'test_result_message']);

        $this->validate([
            'test_phone' => 'required|string|min:8|max:25',
        ], [
            'test_phone.required' => 'Nomor HP tujuan uji coba wajib diisi.',
            'test_phone.min'      => 'Nomor HP tidak valid (minimal 8 digit).',
        ]);

        $token = config('services.wablas.token');
        if (empty($token)) {
            $this->test_result_type = 'error';
            $this->test_result_message = 'WABLAS_TOKEN belum disetel pada file environment (.env). Silakan hubungi teknisi server untuk mengisi token Wablas.';
            return;
        }

        $appName = CompanySetting::getName() ?: config('app.name', 'SIMVENTRA');
        $timeStr = now()->timezone('Asia/Jakarta')->format('d M Y - H:i:s');

        $message = "🔔 *UJI COBA NOTIFIKASI WHATSAPP*\n"
            . "*Control Tower {$appName}*\n\n"
            . "Halo! Ini adalah pesan uji coba dari sistem *{$appName}*.\n\n"
            . "⚙️ *Status Konfigurasi Saat Ini:*\n"
            . "• Toleransi GPS Mati : *{$this->heartbeat_timeout_minutes} Menit*\n"
            . "• Interval Pengulangan Alert : *Setiap {$this->heartbeat_cooldown_minutes} Menit*\n"
            . "• Status Master Alert : *" . ($this->wa_alert_enabled ? 'AKTIF' : 'NONAKTIF') . "*\n"
            . "• Notif ke Sopir : *" . ($this->wa_notify_driver ? 'AKTIF' : 'NONAKTIF') . "*\n"
            . "• Notif ke Dispatcher : *" . ($this->wa_notify_dispatcher ? 'AKTIF' : 'NONAKTIF') . "*\n"
            . "• Waktu Pengujian : {$timeStr} WIB\n\n"
            . "✅ _Jika Anda membaca pesan ini, integrasi WhatsApp Gateway Wablas telah berfungsi dengan sempurna!_";

        $success = WhatsAppService::send($this->test_phone, $message);

        if ($success) {
            $this->test_result_type = 'success';
            $this->test_result_message = "Pesan uji coba berhasil dikirim ke " . WhatsAppService::normalizePhone($this->test_phone) . "! Silakan cek WhatsApp Anda.";
        } else {
            $this->test_result_type = 'error';
            $this->test_result_message = "Gagal mengirim pesan ke {$this->test_phone}. Pastikan nomor HP aktif dan akun Wablas Anda memiliki kuota pesan.";
        }
    }

    public function render()
    {
        $hasWablasToken = !empty(config('services.wablas.token'));
        $wablasBaseUrl  = config('services.wablas.base_url', 'https://solo.wablas.com');

        return view('livewire.settings.alert-settings', [
            'hasWablasToken' => $hasWablasToken,
            'wablasBaseUrl'  => $wablasBaseUrl,
        ])->layout('layouts.app', ['title' => 'Pengaturan Alert GPS & Notifikasi WhatsApp']);
    }
}
