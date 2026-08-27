<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureRecentAdminTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($request->is('admin/security*') || $request->is('admin/2fa/*') || ! $user?->isSuperAdmin() || ! config('admin-security.two_factor_enforced')) {
            return $next($request);
        }

        if ($user->two_factor_confirmed_at === null) {
            abort(403, 'Super Admin phải đăng ký xác thực hai lớp trước khi tiếp tục.');
        }

        $verifiedAt = $request->session()->get('admin_2fa_verified_at');
        $limit = now()->subMinutes((int) config('admin-security.challenge_window_minutes'));

        if (is_string($verifiedAt) && Carbon::parse($verifiedAt)->greaterThanOrEqualTo($limit)) {
            return $next($request);
        }

        return redirect()->route('admin.two-factor.verify');
    }
}
