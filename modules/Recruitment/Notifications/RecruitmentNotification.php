<?php

declare(strict_types=1);

namespace Modules\Recruitment\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class RecruitmentNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $subject,
        private readonly string $message,
        private readonly ?string $url = null,
    ) {}

    /** @return list<'database'|'mail'> */
    public function via(User $notifiable): array
    {
        return ['database', 'mail'];
    }

    /** @return array{icon:string,message:string,url:string|null} */
    public function toDatabase(User $notifiable): array
    {
        return ['icon' => 'mail', 'message' => $this->message, 'url' => $this->url];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->subject)
            ->greeting('Xin chào '.$notifiable->name.'!')
            ->line($this->message)
            ->line('Thông tin liên hệ vẫn được bảo vệ theo lựa chọn của bạn.');

        return $this->url ? $mail->action('Mở DSCons', $this->url) : $mail;
    }
}
