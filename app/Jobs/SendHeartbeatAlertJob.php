<?php

namespace App\Jobs;

use App\Models\HeartbeatAlert;
use App\Models\User;
use App\Models\VehicleAssignment;
use App\Services\PushNotificationService;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job: SendHeartbeatAlertJob
 *
 * Dikirim ke queue saat heartbeat checker mendeteksi sopir tidak
 * mengirim lokasi GPS dalam batas waktu yang ditentukan.
 *
 * Yang dilakukan job ini:
 *  1. Kirim Push Notification ke sopir (buka kembali aplikasi)
 *  2. Kirim WhatsApp ke nomor HP sopir via Wablas
 *  3. Kirim Push Notification ke semua Dispatcher / Fleet Officer
 *  4. Catat log ke tabel heartbeat_alerts
 */
class SendHeartbeatAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 30; // detik antar retry

    public function __construct(
        public VehicleAssignment $assignment,
        public int $silenceMinutes
    ) {}

    public function handle(): void
    {
        $driver   = $this->assignment->driver;
        $vehicle  = $this->assignment->vehicle;

        if (!$driver || !$vehicle) {
            Log::warning('[Heartbeat]: Assignment #' . $this->assignment->id . ' tidak memiliki driver/vehicle, dilewati.');
            return;
        }

        $driverName   = $driver->name;
        $plateNumber  = strtoupper($vehicle->license_plate);
        $destination  = $this->assignment->destination ?? '-';
        $minutes      = $this->silenceMinutes;

        Log::info("[Heartbeat]: Memproses alert untuk sopir {$driverName} ({$plateNumber}) – diam {$minutes} menit.");

        $pushDriver     = false;
        $pushDispatcher = false;
        $waSent         = false;
        $errorNotes     = [];

        // ─────────────────────────────────────────────────────────────────
        // 1. Push Notification → Sopir
        // ─────────────────────────────────────────────────────────────────
        $driverUser = $driver->user;
        if ($driverUser) {
            $pushDriver = PushNotificationService::sendToUser(
                $driverUser,
                '⚠️ Tracking GPS Tidak Aktif',
                "Sistem tidak menerima lokasi Anda selama {$minutes} menit. Mohon buka kembali aplikasi Simventra.",
                [
                    'type'          => 'heartbeat_alert',
                    'assignment_id' => $this->assignment->id,
                ]
            );

            if (!$pushDriver) {
                $errorNotes[] = 'Push ke sopir gagal (token kosong atau Expo error)';
            }
        } else {
            $errorNotes[] = 'Sopir tidak memiliki akun User terhubung, push dilewati';
        }

        // ─────────────────────────────────────────────────────────────────
        // 2. WhatsApp → Sopir (via Wablas)
        // ─────────────────────────────────────────────────────────────────
        if (!empty($driver->phone)) {
            $waMessage = $this->buildDriverWaMessage($driverName, $plateNumber, $destination, $minutes);
            $waSent    = WhatsAppService::send($driver->phone, $waMessage);

            if (!$waSent) {
                $errorNotes[] = 'WA ke sopir gagal';
            }
        } else {
            $errorNotes[] = 'Sopir tidak memiliki nomor HP, WA dilewati';
        }

        // ─────────────────────────────────────────────────────────────────
        // 3. Push Notification → Semua Dispatcher / Fleet Officer
        // ─────────────────────────────────────────────────────────────────
        $dispatchers = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Super Admin', 'Fleet Officer', 'Control Tower Officer']);
        })->where('is_active', true)->get();

        $dispatcherCount = 0;
        foreach ($dispatchers as $dispatcher) {
            $sent = PushNotificationService::sendToUser(
                $dispatcher,
                '🚨 Alert: Tracking Sopir Hilang',
                "Sopir {$driverName} ({$plateNumber}) tidak mengirim lokasi selama {$minutes} menit. Tujuan: {$destination}.",
                [
                    'type'          => 'heartbeat_alert_dispatcher',
                    'assignment_id' => $this->assignment->id,
                    'driver_name'   => $driverName,
                    'vehicle'       => $plateNumber,
                ]
            );
            if ($sent) $dispatcherCount++;
        }

        $pushDispatcher = $dispatcherCount > 0;

        if (!$pushDispatcher && $dispatchers->count() > 0) {
            $errorNotes[] = "Push ke {$dispatchers->count()} dispatcher gagal semua";
        }

        // ─────────────────────────────────────────────────────────────────
        // 4. Catat log ke heartbeat_alerts
        // ─────────────────────────────────────────────────────────────────
        HeartbeatAlert::create([
            'assignment_id'        => $this->assignment->id,
            'driver_id'            => $this->assignment->driver_id,
            'vehicle_id'           => $this->assignment->vehicle_id,
            'last_ping_at'         => $this->assignment->last_ping_at,
            'silence_minutes'      => $this->silenceMinutes,
            'push_sent_driver'     => $pushDriver,
            'push_sent_dispatcher' => $pushDispatcher,
            'wa_sent'              => $waSent,
            'error_notes'          => !empty($errorNotes) ? implode('; ', $errorNotes) : null,
        ]);

        // Update timestamp alert terakhir di assignment (untuk cooldown)
        $this->assignment->update(['heartbeat_alerted_at' => now()]);

        Log::info("[Heartbeat]: Alert selesai untuk {$driverName} – Push sopir: " . ($pushDriver ? '✓' : '✗') .
            " | WA: " . ($waSent ? '✓' : '✗') .
            " | Push dispatcher ({$dispatcherCount}): " . ($pushDispatcher ? '✓' : '✗'));
    }

    /**
     * Bangun pesan WhatsApp yang informatif untuk sopir
     */
    private function buildDriverWaMessage(
        string $driverName,
        string $plateNumber,
        string $destination,
        int $silenceMinutes
    ): string {
        $appName = config('app.name', 'Simventra');
        $time    = now()->timezone('Asia/Jakarta')->format('H:i');

        return "🚨 *PERINGATAN SISTEM {$appName}*\n\n"
            . "Yth. *{$driverName}*,\n\n"
            . "Sistem kami tidak menerima sinyal lokasi GPS Anda selama *{$silenceMinutes} menit* terakhir.\n\n"
            . "📋 *Detail Penugasan:*\n"
            . "• Kendaraan : {$plateNumber}\n"
            . "• Tujuan     : {$destination}\n"
            . "• Pukul      : {$time} WIB\n\n"
            . "⚠️ *Tindakan yang diperlukan:*\n"
            . "Mohon segera buka kembali aplikasi *{$appName}* agar tracking perjalanan Anda dapat terpantau oleh Control Tower.\n\n"
            . "_Jika Anda mengalami masalah atau kedaruratan, segera hubungi dispatcher._";
    }
}
