<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RecruitmentNotification extends Notification
{
    use Queueable;

    public function __construct(private string $subject, private string $message, private ?string $url = null) {}

    public function via($notifiable): array { return ['database', 'mail']; }

    public function toDatabase($notifiable): array
    {
        return ['icon' => 'mail', 'message' => $this->message, 'url' => $this->url];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)->subject($this->subject)->greeting('Xin chào '.$notifiable->name.'!')->line($this->message)->line('Thông tin liên hệ vẫn được bảo vệ theo lựa chọn của bạn.');
        return $this->url ? $mail->action('Mở DSCons', $this->url) : $mail;
    }
}
