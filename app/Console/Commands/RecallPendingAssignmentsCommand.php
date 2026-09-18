<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\VehicleAssignment;
use App\Services\PushNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RecallPendingAssignmentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simventra:recall-assignments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim ulang notifikasi darurat ke HP sopir jika tugas belum dikonfirmasi dalam 2 menit';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Cari penugasan yang masih status 'assigned' dan sudah lebih dari 2 menit belum dikonfirmasi
        $pendingAssignments = VehicleAssignment::with(['vehicle', 'driver.user'])
            ->where('status', 'assigned')
            ->where('created_at', '<=', now()->subMinutes(2))
            ->get();

        if ($pendingAssignments->isEmpty()) {
            $this->info('Tidak ada penugasan tertunda yang memerlukan recall.');
            return self::SUCCESS;
        }

        $recalledCount = 0;

        foreach ($pendingAssignments as $assignment) {
            // Batasi pengiriman auto-recall agar hanya berdering ulang setiap 2 menit sekali per tugas
            $cacheKey = 'auto_recall_sent_' . $assignment->id;
            if (Cache::has($cacheKey)) {
                continue;
            }

            $driver = $assignment->driver;
            $driverUser = $driver?->user;

            // Fallback cari akun jika belum terhubung
            if (!$driverUser && $driver) {
                $driverUser = User::where('email', $driver->email)
                    ->orWhere('phone', $driver->phone)
                    ->first();

                if (!$driverUser && !empty($driver->phone)) {
                    $suffix = substr(preg_replace('/\D/', '', $driver->phone), -7);
                    $driverUser = User::where('phone', 'LIKE', '%' . $suffix)->first();
                }

                if ($driverUser) {
                    $driver->update(['user_id' => $driverUser->id]);
                }
            }

            if ($driverUser && !empty($driverUser->push_token)) {
                $elapsedMinutes = (int) now()->diffInMinutes($assignment->created_at);

                $sent = PushNotificationService::sendToUser(
                    $driverUser,
                    '🚨 PANGGILAN DARURAT: ' . $assignment->vehicle->license_plate,
                    "Tugas ke {$assignment->destination} belum dikonfirmasi ({$elapsedMinutes} mnt lalu). Buka aplikasi sekarang!",
                    [
                        'assignment_id' => $assignment->id,
                        'license_plate' => $assignment->vehicle->license_plate,
                        'status'        => 'assigned',
                        'type'          => 'auto_recall',
                    ]
                );

                if ($sent) {
                    Cache::put($cacheKey, now()->toDateTimeString(), now()->addMinutes(2));
                    $recalledCount++;
                    Log::info("[AutoRecall]: Push notification recall berhasil dikirim ke sopir {$driver->name} (Armada {$assignment->vehicle->license_plate})");
                    $this->info("Recall terkirim ke: {$driver->name} ({$assignment->vehicle->license_plate})");
                }
            }
        }

        $this->info("Selesai. Total {$recalledCount} notifikasi recall terkirim.");
        return self::SUCCESS;
    }
}
