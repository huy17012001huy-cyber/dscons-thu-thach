<?php

namespace App\Http\Controllers;

use App\Exceptions\GoogleAuthException;
use App\Services\GoogleAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

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

        return Socialite::driver('google')
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
            if (!$membership || $membership->status === 'banned' || !$hasAccess) {
                return $this->rejectLogin($request, 'Tài khoản của bạn đã bị khóa hoặc hết quyền truy cập.');
            }

            Auth::login($user);
            $request->session()->regenerate();

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
