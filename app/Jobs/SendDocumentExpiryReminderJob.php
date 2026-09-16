<?php

namespace App\Jobs;

use App\Models\DocumentReminder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendDocumentExpiryReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public DocumentReminder $reminder
    ) {}

    public function handle(): void
    {
        try {
            // Kirim ke semua user dengan role terkait
            $recipients = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['Super Admin', 'HR', 'Fleet Officer', 'Maintenance Officer']);
            })->where('is_active', true)->get();

            $subject = "⚠️ Reminder Dokumen: {$this->reminder->document_title}";
            $body = $this->buildEmailBody();

            foreach ($recipients as $user) {
                // Gunakan Laravel built-in mail (dapat dikustomisasi)
                \Illuminate\Support\Facades\Notification::route('mail', $user->email)
                    ->notify(new \App\Notifications\DocumentExpiryNotification($this->reminder));
            }

            $this->reminder->update([
                'status'  => 'sent',
                'sent_at' => now(),
            ]);

            Log::info("Document expiry reminder sent", [
                'reminder_id'    => $this->reminder->id,
                'document_title' => $this->reminder->document_title,
                'expiry_date'    => $this->reminder->expiry_date,
                'days_before'    => $this->reminder->days_before,
            ]);
        } catch (\Exception $e) {
            $this->reminder->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Failed to send document expiry reminder", [
                'reminder_id' => $this->reminder->id,
                'error'       => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function buildEmailBody(): string
    {
        $daysText = $this->reminder->days_before . ' hari';
        return "Dokumen <strong>{$this->reminder->document_title}</strong> untuk <strong>{$this->reminder->related_name}</strong> akan kadaluarsa dalam {$daysText} ({$this->reminder->expiry_date->format('d/m/Y')}).";
    }
}
