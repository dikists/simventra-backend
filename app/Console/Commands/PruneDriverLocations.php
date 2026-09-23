<?php

namespace App\Console\Commands;

use App\Models\DriverLocation;
use Illuminate\Console\Command;

/**
 * Command: simventra:prune-locations
 *
 * Membersihkan data lokasi GPS lama agar database tidak membengkak.
 * Data GPS yang sudah melebihi batas retensi akan dihapus.
 *
 * Dijalankan oleh scheduler setiap hari pukul 02:00 WIB.
 */
class PruneDriverLocations extends Command
{
    protected $signature = 'simventra:prune-locations
                            {--days= : Override jumlah hari retensi}
                            {--dry-run : Tampilkan jumlah yang akan dihapus tanpa menghapus}';

    protected $description = 'Hapus data lokasi GPS sopir yang lebih lama dari periode retensi';

    public function handle(): int
    {
        $retentionDays = (int) ($this->option('days') ?? config('simventra.gps.retention_days', 30));
        $isDryRun = $this->option('dry-run');
        $cutoffDate = now()->subDays($retentionDays);

        $this->info("🧹 Prune GPS locations older than {$retentionDays} days (before {$cutoffDate->format('Y-m-d')})...");

        // Hitung jumlah record yang akan dihapus
        $count = DriverLocation::where('recorded_at', '<', $cutoffDate)->count();

        if ($count === 0) {
            $this->info('   ↳ Tidak ada data GPS lama yang perlu dihapus.');
            return Command::SUCCESS;
        }

        $this->line("   ↳ Ditemukan {$count} record GPS yang lebih lama dari {$retentionDays} hari.");

        if ($isDryRun) {
            $this->warn("   [DRY-RUN] {$count} record akan dihapus jika dijalankan tanpa --dry-run.");
            return Command::SUCCESS;
        }

        // Hapus secara batch untuk menghindari locking table terlalu lama
        $deleted = 0;
        $batchSize = 5000;

        do {
            $affected = DriverLocation::where('recorded_at', '<', $cutoffDate)
                ->limit($batchSize)
                ->delete();

            $deleted += $affected;

            if ($affected > 0) {
                $this->line("   ↳ Dihapus batch: {$affected} record (total: {$deleted})");
            }
        } while ($affected > 0);

        $this->info("✅ Selesai. Total {$deleted} record GPS lama berhasil dihapus.");

        return Command::SUCCESS;
    }
}
