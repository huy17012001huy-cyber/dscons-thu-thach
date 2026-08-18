<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class GenericNotification extends Notification
{
    public function __construct(
        private string $icon,
        private string $message,
        private ?string $url = null,
        private ?int $postId = null,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'icon' => $this->icon,
            'message' => $this->message,
            'url' => $this->url,
            'post_id' => $this->postId,
        ];
    }
}
