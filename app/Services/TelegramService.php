<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    public static function sendToAdmin(string $message): void
    {
        self::send($message, config('services.telegram.admin_topic_id'));
    }

    // Thông báo member hoàn thành sạch 21 ngày → gửi vào topic riêng cùng group
    public static function sendCompletion(string $message): void
    {
        self::send($message, config('services.telegram.completion_topic_id'));
    }

    private static function send(string $message, $topicId = null): void
    {
        $token = config('services.telegram.bot_token');
        $chatId = config('services.telegram.admin_chat_id');

        if (!$token || !$chatId) return;

        $payload = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        // Group dạng forum/chủ đề → gửi vào đúng topic
        if ($topicId) {
            $payload['message_thread_id'] = $topicId;
        }

        try {
            Http::timeout(5)->post("https://api.telegram.org/bot{$token}/sendMessage", $payload);
        } catch (\Throwable $e) {
            Log::warning('Telegram notification failed: ' . $e->getMessage());
        }
    }
}
