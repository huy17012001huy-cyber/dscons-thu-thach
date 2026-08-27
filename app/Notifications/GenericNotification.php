<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;

class GenericNotification extends Notification
{
    public function __construct(
        private string $icon,
        private string $message,
        private ?string $url = null,
        private ?int $postId = null,
    ) {}

    /** @return list<'database'> */
    public function via(User $notifiable): array
    {
        return ['database'];
    }

    /** @return array{icon: string, message: string, url: string|null, post_id: int|null} */
    public function toDatabase(User $notifiable): array
    {
        return [
            'icon' => $this->icon,
            'message' => $this->message,
            'url' => $this->url,
            'post_id' => $this->postId,
        ];
    }
}
