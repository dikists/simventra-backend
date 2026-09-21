<?php

namespace App\Console\Commands;

use App\Jobs\SendHeartbeatAlertJob;
use App\Models\VehicleAssignment;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Command: simventra:check-driver-heartbeat
 *
 * Dijalankan oleh Scheduler setiap 2 menit.
 * Mendeteksi sopir yang sedang on_trip namun tidak mengirim
 * lokasi GPS dalam batas waktu yang dikonfigurasi.
 *
 * Alur:
 *  1. Query semua assignment berstatus `on_trip`
 *  2. Cek apakah last_ping_at melebihi HEARTBEAT_TIMEOUT_MINUTES
 *  3. Cek cooldown: skip jika heartbeat_alerted_at < HEARTBEAT_COOLDOWN_MINUTES yang lalu
 *  4. Dispatch SendHeartbeatAlertJob ke queue
 */
class CheckDriverHeartbeat extends Command
{
    protected $signature = 'simventra:check-driver-heartbeat
                            {--dry-run : Tampilkan daftar tanpa mengirim alert}';

    protected $description = 'Periksa sopir yang tidak mengirim lokasi GPS (heartbeat timeout) dan kirim alert';

    public function handle(): int
    {
        /** @var int $timeoutMinutes Berapa menit diam sebelum dianggap hilang sinyal */
        $timeoutMinutes  = (int) config('simventra.heartbeat.timeout_minutes', 5);

        /** @var int $cooldownMinutes Minimum jeda antar alert ke assignment yang sama (anti-spam) */
        $cooldownMinutes = (int) config('simventra.heartbeat.cooldown_minutes', 10);

        $isDryRun  = $this->option('dry-run');
        $cutoffAt  = now()->subMinutes($timeoutMinutes);
        $alertCount = 0;

        $this->info("🔍 Memeriksa heartbeat sopir (timeout: {$timeoutMinutes} mnt, cooldown: {$cooldownMinutes} mnt)...");

        // Ambil semua assignment yang sedang dalam perjalanan
        $assignments = VehicleAssignment::with(['driver', 'vehicle'])
            ->where('status', 'on_trip')
            ->get();

        if ($assignments->isEmpty()) {
            $this->line('   ↳ Tidak ada kendaraan yang sedang dalam perjalanan.');
            return Command::SUCCESS;
        }

        $this->line("   ↳ Ditemukan {$assignments->count()} kendaraan aktif on_trip.");

        foreach ($assignments as $assignment) {
            $driverName  = $assignment->driver?->name ?? 'Unknown';
            $plate       = strtoupper($assignment->vehicle?->license_plate ?? '-');
            $destination = $assignment->destination ?? '-';

            // ── Cek 1: last_ping_at pernah diisi?
            if (is_null($assignment->last_ping_at)) {
                // Belum pernah ada sinyal sama sekali – gunakan departure_time sebagai fallback
                $referenceTime = $assignment->departure_time ?? $assignment->updated_at;
            } else {
                $referenceTime = $assignment->last_ping_at;
            }

            // ── Cek 2: Apakah sudah melewati batas timeout?
            if (!Carbon::instance($referenceTime)->isBefore($cutoffAt)) {
                // Masih dalam batas waktu normal, skip
                continue;
            }

            $silenceMinutes = max(0, (int) abs(now()->diffInMinutes($referenceTime)));

            // ── Cek 3: Cooldown – jangan spam alert
            if (!is_null($assignment->heartbeat_alerted_at)) {
                $lastAlertMinutesAgo = (int) now()->diffInMinutes($assignment->heartbeat_alerted_at);
                if ($lastAlertMinutesAgo < $cooldownMinutes) {
                    $this->line("   ⏳ Skip [{$plate}] {$driverName} – alert sudah dikirim {$lastAlertMinutesAgo} mnt lalu (cooldown {$cooldownMinutes} mnt).");
                    continue;
                }
            }

            // ── Alert terdeteksi
            if ($isDryRun) {
                $this->warn("   🔔 [DRY-RUN] [{$plate}] {$driverName} → {$destination} | Diam: {$silenceMinutes} mnt | Ping terakhir: {$referenceTime}");
                $alertCount++;
                continue;
            }

            $this->warn("   🚨 Alert! [{$plate}] {$driverName} → {$destination} | Diam: {$silenceMinutes} mnt");

            SendHeartbeatAlertJob::dispatch($assignment, $silenceMinutes)
                ->onQueue('notifications');

            $alertCount++;
        }

        if ($isDryRun) {
            $this->info("✅ [DRY-RUN] {$alertCount} alert yang akan dikirim.");
        } else {
            $this->info("✅ {$alertCount} alert heartbeat berhasil dijadwalkan ke queue.");
        }

        return Command::SUCCESS;
    }
}
