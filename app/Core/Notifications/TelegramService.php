<?php

declare(strict_types=1);

namespace App\Core\Notifications;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class TelegramService
{
    public static function sendToAdmin(string $message): void
    {
        self::send($message, config('services.telegram.admin_topic_id'));
    }

    public static function sendCompletion(string $message): void
    {
        self::send($message, config('services.telegram.completion_topic_id'));
    }

    private static function send(string $message, int|string|null $topicId = null): void
    {
        $token = config('services.telegram.bot_token');
        $chatId = config('services.telegram.admin_chat_id');
        if (! $token || ! $chatId) {
            return;
        }

        $payload = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];
        if ($topicId) {
            $payload['message_thread_id'] = $topicId;
        }

        try {
            Http::timeout(5)->post("https://api.telegram.org/bot{$token}/sendMessage", $payload);
        } catch (\Throwable $exception) {
            Log::warning('Telegram notification failed: '.$exception->getMessage());
        }
    }
}
