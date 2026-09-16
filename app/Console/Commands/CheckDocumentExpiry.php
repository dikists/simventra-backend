<?php

namespace App\Console\Commands;

use App\Jobs\SendDocumentExpiryReminderJob;
use App\Models\DocumentReminder;
use App\Models\EmployeeDocument;
use App\Models\VehicleDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckDocumentExpiry extends Command
{
    protected $signature = 'simventra:check-document-expiry
                            {--dry-run : Tampilkan daftar dokumen tanpa mengirim reminder}';

    protected $description = 'Periksa dokumen yang akan kadaluarsa dan kirim reminder H-30, H-14, H-7';

    private array $daysBefore = [30, 14, 7];

    public function handle(): int
    {
        $this->info('🔍 Memeriksa dokumen yang akan kadaluarsa...');
        $isDryRun = $this->option('dry-run');
        $count    = 0;

        foreach ($this->daysBefore as $days) {
            $targetDate = Carbon::today()->addDays($days);

            // === Vehicle Documents ===
            $vehicleDocs = VehicleDocument::with('vehicle')
                ->where('status', 'active')
                ->whereDate('expiry_date', $targetDate)
                ->get();

            foreach ($vehicleDocs as $doc) {
                $relatedName = $doc->vehicle?->license_plate . ' – ' . $doc->vehicle?->brand;
                $count += $this->processDocument($doc, $days, $relatedName, $isDryRun);
            }

            // === Employee Documents ===
            $employeeDocs = EmployeeDocument::with('employee')
                ->where('status', 'active')
                ->whereDate('expiry_date', $targetDate)
                ->get();

            foreach ($employeeDocs as $doc) {
                $relatedName = $doc->employee?->name . ' (' . $doc->employee?->employee_number . ')';
                $count += $this->processDocument($doc, $days, $relatedName, $isDryRun);
            }
        }

        if ($isDryRun) {
            $this->warn("🔍 DRY RUN: {$count} reminder yang akan dikirim (tidak ada yang terkirim).");
        } else {
            $this->info("✅ {$count} reminder berhasil dijadwalkan ke queue.");
        }

        return Command::SUCCESS;
    }

    private function processDocument(
        EmployeeDocument|VehicleDocument $doc,
        int $days,
        string $relatedName,
        bool $isDryRun
    ): int {
        // Cek apakah reminder sudah dibuat sebelumnya
        $existingReminder = DocumentReminder::where('documentable_type', get_class($doc))
            ->where('documentable_id', $doc->id)
            ->where('days_before', $days)
            ->whereIn('status', ['pending', 'sent'])
            ->exists();

        if ($existingReminder) {
            return 0;
        }

        if ($isDryRun) {
            $this->line("  - [H-{$days}] {$doc->title} ({$relatedName}) – {$doc->expiry_date->format('d/m/Y')}");
            return 1;
        }

        $reminder = DocumentReminder::create([
            'documentable_type' => get_class($doc),
            'documentable_id'   => $doc->id,
            'document_type'     => $doc->document_type,
            'document_title'    => $doc->title,
            'related_name'      => $relatedName,
            'expiry_date'       => $doc->expiry_date,
            'days_before'       => $days,
            'channel'           => 'email',
            'status'            => 'pending',
        ]);

        SendDocumentExpiryReminderJob::dispatch($reminder);

        return 1;
    }
}
