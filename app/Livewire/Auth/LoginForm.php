<?php

namespace App\Livewire\Auth;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Rule;
use Livewire\Component;

class LoginForm extends Component
{
    #[Rule('required|email')]
    public string $email = '';

    #[Rule('required|min:8')]
    public string $password = '';

    public bool $remember = false;

    public string $error = '';

    public string $fingerprint = '';

    public string $redirectTo = '';

    public function mount(): void
    {
        $redirect = request()->query('redirect');
        if (is_string($redirect) && str_starts_with($redirect, '/') && ! str_starts_with($redirect, '//')) {
            $this->redirectTo = $redirect;
            session()->put('auth_redirect', $redirect);
        }
    }

    public function login(): void
    {
        if (config('auth.mode') !== 'password') {
            $this->error = 'Môi trường này chỉ hỗ trợ đăng nhập bằng Google.';

            return;
        }

        $this->validate();

        $throttleKey = Str::lower(trim($this->email)).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->error = 'Bạn đã thử quá nhiều lần. Vui lòng thử lại sau '.$seconds.' giây.';

            return;
        }

        if ($this->fingerprint !== '') {
            session()->put('_login_fp', $this->fingerprint);
        }

        if (! Auth::attempt([
            'email' => trim($this->email),
            'password' => $this->password,
        ], $this->remember)) {
            session()->forget('_login_fp');
            RateLimiter::hit($throttleKey);
            $this->error = 'Email hoặc mật khẩu không đúng.';

            return;
        }

        RateLimiter::clear($throttleKey);
        session()->regenerate();

        $user = Auth::user();
        if (! $user instanceof User) {
            Auth::logout();
            $this->error = 'Không thể xác thực tài khoản. Vui lòng thử lại.';

            return;
        }

        if (Setting::get('email_verification_required', '1') !== '0' && ! $user->hasVerifiedEmail()) {
            $this->redirect(route('verification.notice'), navigate: true);

            return;
        }

        $redirect = session()->pull('auth_redirect');
        $this->redirect($redirect ?: ($user->class ? route('feed') : route('onboarding')), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.login-form')
            ->layout('layouts.guest', ['title' => 'Đăng nhập — '.brand()->name]);
    }
}
