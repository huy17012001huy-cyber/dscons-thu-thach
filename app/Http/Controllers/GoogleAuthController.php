<?php

namespace App\Http\Controllers;

use App\Core\Auth\GoogleAuthService;
use App\Exceptions\GoogleAuthException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $redirect = $request->query('redirect');
        if (is_string($redirect) && str_starts_with($redirect, '/') && ! str_starts_with($redirect, '//')) {
            $request->session()->put('auth_redirect', $redirect);
        }
        if (blank(config('services.google.client_id'))
            || blank(config('services.google.client_secret'))
            || blank(config('services.google.redirect'))) {
            return redirect()->route('login')->with(
                'error',
                'Đăng nhập Google chưa được cấu hình cho môi trường này.'
            );
        }

        /** @var GoogleProvider $provider */
        $provider = Socialite::driver('google');

        return $provider
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(Request $request, GoogleAuthService $googleAuth): RedirectResponse
    {
        try {
            $user = $googleAuth->resolveUser(
                Socialite::driver('google')->user()
            );

            $membership = $user->membership;
            $hasAccess = $membership && ($membership->isActive() || $membership->isTrial());
            if (! $membership || $membership->status === 'banned' || ! $hasAccess) {
                return $this->rejectLogin($request, 'Tài khoản của bạn đã bị khóa hoặc hết quyền truy cập.');
            }

            if ($user->isSuperAdmin() && config('admin-security.two_factor_enforced')) {
                if ($user->two_factor_confirmed_at === null) {
                    return $this->rejectLogin($request, 'Super Admin phải đăng ký xác thực hai lớp trước khi đăng nhập.');
                }

                $request->session()->put([
                    'admin_2fa_pending_user_id' => $user->id,
                    'admin_2fa_redirect' => $request->session()->pull('auth_redirect', route('admin.dashboard')),
                ]);

                return redirect()->route('admin.two-factor.challenge');
            }

            Auth::login($user);
            $request->session()->regenerate();

            if ($user->isSuperAdmin() && $user->two_factor_confirmed_at !== null) {
                $request->session()->put('admin_2fa_verified_at', now()->toIso8601String());
            }

            $redirect = $request->session()->pull('auth_redirect');

            return $redirect ? redirect()->to($redirect) : redirect()->route($user->class ? 'feed' : 'onboarding');
        } catch (GoogleAuthException $exception) {
            return redirect()->route('login')->with('error', $exception->getMessage());
        } catch (\Throwable $exception) {
            Log::warning('Google authentication failed', [
                'exception' => $exception::class,
            ]);
            report($exception);

            return redirect()->route('login')->with(
                'error',
                'Đăng nhập Google không thành công. Vui lòng thử lại hoặc liên hệ Admin.'
            );
        }
    }

    private function rejectLogin(Request $request, string $message): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('error', $message);
    }
}
