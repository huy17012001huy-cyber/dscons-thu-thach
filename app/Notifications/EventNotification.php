<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $subject,
        private string $message,
        private string $url,
    ) {}

    /** @return list<'database'|'mail'> */
    public function via(User $notifiable): array
    {
        // Mail uses the application's configured driver (log on local/staging).
        return ['database', 'mail'];
    }

    /** @return array{icon: string, message: string, url: string} */
    public function toDatabase(User $notifiable): array
    {
        return [
            'icon' => '📅',
            'message' => $this->message,
            'url' => $this->url,
        ];
    }

    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->greeting('Chào '.($notifiable->name ?: 'bạn').'!')
            ->line($this->message)
            ->action('Xem sự kiện', $this->url)
            ->line('Bạn có thể xem lại lịch và thông tin tham gia trong mục Sự kiện của DSCons.');
    }
}
