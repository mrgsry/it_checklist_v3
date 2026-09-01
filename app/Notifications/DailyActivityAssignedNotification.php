<?php

namespace App\Notifications;

use App\Models\DailyActivity;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyActivityAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly DailyActivity $activity) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Daily Activity baru ditugaskan: '.$this->activity->activity)
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Anda menerima tugas Daily Activity baru dari admin.')
            ->line('Aktivitas: '.$this->activity->activity)
            ->line('Tanggal: '.$this->activity->activity_date->isoFormat('D MMMM Y'));

        if ($this->activity->notes) {
            $mail->line('Catatan admin: '.$this->activity->notes);
        }

        return $mail->action('Buka Daily Activity', route('user.daily-activities.index', [
            'date' => $this->activity->activity_date->toDateString(),
        ]))->line('Status dan catatan progres dapat diperbarui dari halaman tersebut.');
    }
}
