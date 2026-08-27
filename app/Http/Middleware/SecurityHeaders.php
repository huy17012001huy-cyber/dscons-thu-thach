<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = (array) config('security.headers', []);
        $response->headers->set('X-Frame-Options', (string) ($headers['x_frame_options'] ?? 'SAMEORIGIN'));
        $response->headers->set('X-Content-Type-Options', (string) ($headers['x_content_type_options'] ?? 'nosniff'));
        $response->headers->set('Referrer-Policy', (string) ($headers['referrer_policy'] ?? 'strict-origin-when-cross-origin'));
        $response->headers->set('Permissions-Policy', (string) ($headers['permissions_policy'] ?? 'camera=(), microphone=(), geolocation=(), payment=()'));
        $response->headers->set('Content-Security-Policy-Report-Only', (string) config('security.csp'));

        if ($request->isSecure() && ! app()->environment('local')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
