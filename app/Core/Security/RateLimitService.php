<?php

declare(strict_types=1);

namespace App\Core\Security;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

final class RateLimitService
{
    public static function register(): void
    {
        RateLimiter::for('google-auth', fn (Request $request): Limit => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('sepay-webhook', fn (Request $request): Limit => Limit::perMinute(120)->by($request->ip()));
        RateLimiter::for('revit-device-start', fn (Request $request): Limit => Limit::perMinute(8)->by($request->ip()));
        RateLimiter::for('revit-device-poll', fn (Request $request): Limit => Limit::perMinute(30)->by($request->ip()));
        RateLimiter::for('revit-heartbeat', fn (Request $request): Limit => Limit::perMinute(120)->by($request->bearerToken() ?: $request->ip()));
        RateLimiter::for('admin-sensitive', function (Request $request): Limit {
            $user = $request->user();

            return Limit::perMinute(20)->by((string) ($user?->getAuthIdentifier() ?? $request->ip()));
        });
    }
}
