<?php

namespace App\Listeners;

use App\Models\LoginLog;
use Illuminate\Auth\Events\Login;

class RecordLoginLog
{
    public function handle(Login $event): void
    {
        try {
            $hasSession = request()->hasSession();

            // Fingerprint hash do client-side JS tính (canvas+webgl+screen+navigator…).
            // LoginForm/RegisterForm put session key '_login_fp' NGAY TRƯỚC Auth::attempt,
            // listener đọc rồi forget. Auth::login chương trình (vd. ImpersonationController)
            // không set → fingerprint = null là đúng (không cần với phiên đóng vai).
            $fp = null;
            if ($hasSession && request()->session()->has('_login_fp')) {
                $fp = (string) request()->session()->get('_login_fp');
                request()->session()->forget('_login_fp');
                // Chỉ chấp nhận hash hex 8-64 ký tự để khỏi inject rác
                if (!preg_match('/^[a-f0-9]{8,64}$/i', $fp)) {
                    $fp = null;
                }
            }

            $cookie = request()->cookie('taip_device_id');

            // DEBUG: log những gì listener thấy được khi event Login fire
            \Log::info('RecordLoginLog firing', [
                'user_id'   => $event->user?->getAuthIdentifier(),
                'cookie'    => $cookie,
                'fp'        => $fp,
                'has_imp'   => $hasSession && request()->session()->has('impersonator_id'),
                'cookies_keys' => $hasSession ? array_keys(request()->cookies->all()) : [],
            ]);

            LoginLog::create([
                'brand_id'         => app()->bound('brand') ? brand()->id : null,
                'user_id'          => $event->user?->getAuthIdentifier(),
                'ip_address'       => request()->ip(),
                'user_agent'       => mb_substr((string) request()->userAgent(), 0, 1000),
                'via_admin'        => $hasSession && request()->session()->has('impersonator_id'),
                'device_cookie_id' => $cookie,
                'fingerprint_hash' => $fp ? mb_substr($fp, 0, 16) : null,
            ]);
        } catch (\Throwable $e) {
            // Không bao giờ chặn đăng nhập chỉ vì ghi log lỗi
            report($e);
        }
    }
}
