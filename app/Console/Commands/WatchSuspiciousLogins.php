<?php

namespace App\Console\Commands;

use App\Models\LoginLog;
use App\Models\Setting;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WatchSuspiciousLogins extends Command
{
    protected $signature = 'aip:watch-logins';

    protected $description = 'Cảnh báo Telegram khi có tài khoản đăng nhập từ IP đang bị theo dõi';

    public function handle(): int
    {
        // IP theo dõi — cấu hình qua Setting "watch_login_ips" (ngăn cách dấu phẩy).
        // Để trống thì cron không làm gì.
        $ips = collect(explode(',', (string) Setting::get('watch_login_ips', '')))
            ->map(fn ($x) => trim($x))->filter()->values()->all();
        if (empty($ips)) {
            return self::SUCCESS;
        }

        // Đã cảnh báo rồi thì khỏi báo lại (dedup theo cặp "ip|user_id")
        $alerted = json_decode((string) Setting::get('watch_login_alerted', '[]'), true) ?: [];
        $alertedSet = array_flip($alerted);

        // Gom (ip, user_id) từ login mới (login_logs) + đang online (sessions)
        $pairs = [];

        LoginLog::whereIn('ip_address', $ips)
            ->whereNotNull('user_id')
            ->where('via_admin', false) // bỏ qua login do admin đóng vai
            ->where('created_at', '>=', now()->subDay())
            ->orderByDesc('created_at')
            ->get(['user_id', 'ip_address', 'created_at'])
            ->each(function ($r) use (&$pairs) {
                $key = $r->ip_address . '|' . $r->user_id;
                if (!isset($pairs[$key])) { // orderByDesc → giữ lần đăng nhập mới nhất
                    $pairs[$key] = ['ip' => $r->ip_address, 'uid' => $r->user_id, 'via' => 'đăng nhập mới', 'at' => Carbon::parse($r->created_at)];
                }
            });

        DB::table('sessions')->whereIn('ip_address', $ips)
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', now()->subHour()->timestamp)
            ->orderByDesc('last_activity')
            ->get(['user_id', 'ip_address', 'last_activity'])
            ->each(function ($r) use (&$pairs) {
                $key = $r->ip_address . '|' . $r->user_id;
                if (!isset($pairs[$key])) {
                    $pairs[$key] = ['ip' => $r->ip_address, 'uid' => $r->user_id, 'via' => 'đang online', 'at' => Carbon::createFromTimestamp((int) $r->last_activity)];
                }
            });

        $new = array_filter($pairs, fn ($k) => !isset($alertedSet[$k]), ARRAY_FILTER_USE_KEY);
        if (empty($new)) {
            return self::SUCCESS;
        }

        foreach ($new as $key => $p) {
            $u = DB::table('users')->where('id', $p['uid'])->first();
            $at = $p['at']->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s');
            $msg = "🚨 <b>Phát hiện đăng nhập từ IP theo dõi của nick ảo Minh Khôi</b>\n"
                 . "IP: <code>{$p['ip']}</code>\n"
                 . 'Tài khoản: <b>' . e($u->name ?? '?') . '</b> (@' . e($u->username ?? '?') . ")\n"
                 . 'Email: ' . e($u->email ?? '?') . "\n"
                 . "Kiểu: {$p['via']}\n"
                 . "Đăng nhập lúc: {$at} (giờ VN)";
            TelegramService::sendToAdmin($msg);
            $alerted[] = $key;
        }

        Setting::set('watch_login_alerted', json_encode(array_values(array_unique($alerted))));
        $this->info('Đã cảnh báo ' . count($new) . ' tài khoản mới từ IP theo dõi.');

        return self::SUCCESS;
    }
}
