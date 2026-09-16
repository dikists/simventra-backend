<?php

namespace App\Notifications;

use App\Models\DocumentReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentExpiryNotification extends Notification
{
    use Queueable;

    public function __construct(
        public DocumentReminder $reminder
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expiryDate  = $this->reminder->expiry_date->format('d F Y');
        $daysBefore  = $this->reminder->days_before;
        $title       = $this->reminder->document_title;
        $relatedName = $this->reminder->related_name;

        return (new MailMessage)
            ->subject("⚠️ SIMVENTRA – Reminder Dokumen H-{$daysBefore}: {$title}")
            ->greeting("Halo,")
            ->line("Dokumen berikut akan **kadaluarsa dalam {$daysBefore} hari**:")
            ->line("")
            ->line("📄 **Dokumen:** {$title}")
            ->line("👤 **Milik:** {$relatedName}")
            ->line("📅 **Tanggal Kadaluarsa:** {$expiryDate}")
            ->action('Lihat Detail di SIMVENTRA', url('/'))
            ->line("Segera perpanjang dokumen sebelum tanggal kadaluarsa untuk memastikan kepatuhan operasional.")
            ->salutation("Hormat kami,\nSISTEM SIMVENTRA");
    }
}
