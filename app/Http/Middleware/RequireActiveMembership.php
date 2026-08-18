<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireActiveMembership
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Admin always bypasses membership check
        if ($user->is_admin) {
            return $next($request);
        }

        // Only redirect to onboarding if Lv10+ and no class
        if ($user->level >= 10 && empty($user->class)) {
            return redirect()->route('onboarding');
        }

        $membership = $user->membership;

        if (!$membership) {
            return redirect()->route('membership.expired')
                ->with('error', 'Bạn chưa có membership tại ' . brand()->name . '.');
        }

        if ($membership->status === 'banned') {
            // Don't logout when admin is impersonating — would strand the admin
            if ($request->session()->has('impersonator_id')) {
                return redirect()->route('membership.expired')
                    ->with('error', 'User này đang bị khóa tại ' . brand()->name . '.');
            }
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Tài khoản đã bị khóa tại ' . brand()->name . '.');
        }

        if ($membership->status === 'expired'
            || ($membership->status === 'trial' && $membership->trial_ends_at?->isPast())
            || ($membership->status === 'active' && $membership->expires_at?->isPast())
        ) {
            return redirect()->route('membership.expired');
        }

        return $next($request);
    }
}
