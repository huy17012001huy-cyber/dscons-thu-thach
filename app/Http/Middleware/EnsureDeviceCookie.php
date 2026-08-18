<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;

class EnsureDeviceCookie
{
    /**
     * Đảm bảo browser có cookie `taip_device_id` (UUID, 5 năm).
     *
     * Cookie persist qua logout/login → mọi account đăng nhập từ cùng browser
     * đều mang cùng cookie_id, dùng để phát hiện nick ảo trong AdminLoginLogs.
     * User clear cookie thì mất, nhưng đa số không bao giờ clear cookie.
     */
    public function handle(Request $request, Closure $next)
    {
        $existing = $request->cookie('taip_device_id');
        $generated = false;

        if (!$existing) {
            $existing = (string) Str::uuid();
            $generated = true;
            // Inject ngay vào request hiện tại để listener (RecordLoginLog) đọc được
            $request->cookies->set('taip_device_id', $existing);
        }

        // DEBUG: log mọi request POST tới /livewire/update để xác minh middleware có chạy
        if ($request->isMethod('POST') && str_contains($request->path(), 'livewire/update')) {
            Log::info('EnsureDeviceCookie ran on livewire/update', [
                'existing'     => $existing,
                'generated'    => $generated,
                'after_inject' => $request->cookie('taip_device_id'),
                'all_cookies'  => array_keys($request->cookies->all()),
            ]);
        }

        $response = $next($request);

        // Dùng Symfony Cookie qua headers->setCookie() thay cho $response->cookie()
        // vì BinaryFileResponse / StreamedResponse / RedirectResponse từ Symfony
        // không có macro cookie() của Laravel — gọi vào sẽ throw fatal error.
        // headers->setCookie tồn tại trên MỌI subclass của Symfony Response.
        $response->headers->setCookie(
            Cookie::create(
                'taip_device_id',
                $existing,
                time() + 60 * 60 * 24 * 365 * 5,  // 5 năm (unix timestamp)
                '/',
                null,
                $request->secure(),
                true,                              // http-only
                false,
                'lax'
            )
        );

        return $response;
    }
}
