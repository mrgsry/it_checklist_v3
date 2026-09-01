<?php

namespace App\Notifications;

use App\Models\ChecklistForm;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FormAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly ChecklistForm $form) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $schedule = match ($this->form->schedule_type) {
            'weekly' => 'Mingguan',
            'custom' => 'Setiap '.$this->form->schedule_interval.' hari',
            default => 'Harian',
        };

        $mail = (new MailMessage)
            ->subject('Checklist baru ditugaskan: '.$this->form->title)
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Anda telah ditugaskan untuk mengisi checklist berikut:')
            ->line('Judul: '.$this->form->title)
            ->line('Jadwal: '.$schedule);

        if ($this->form->description) {
            $mail->line('Deskripsi: '.$this->form->description);
        }

        return $mail
            ->action('Buka Checklist', route('user.checklist.index'))
            ->line('Silakan buka checklist dan lengkapi sesuai jadwal yang berlaku.');
    }
}
