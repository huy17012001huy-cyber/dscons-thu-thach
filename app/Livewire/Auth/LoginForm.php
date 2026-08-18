<?php

namespace App\Livewire\Auth;

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
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

    // Hash truncated SHA-256 client-side tính từ canvas+webgl+screen+navigator.
    // Set bởi inline JS trong view trước khi user nhấn nút Đăng nhập.
    public string $fingerprint = '';

    public function login(): void
    {
        $this->validate();

        $throttleKey = Str::lower($this->email) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->error = 'Bạn đã thử quá nhiều lần. Vui lòng thử lại sau ' . $seconds . ' giây.';
            return;
        }

        // Đẩy fingerprint vào session để RecordLoginLog listener đọc khi event Login fire
        if ($this->fingerprint !== '') {
            session()->put('_login_fp', $this->fingerprint);
        }

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->forget('_login_fp');
            RateLimiter::hit($throttleKey);
            $this->error = 'Email hoặc mật khẩu không đúng.';
            return;
        }

        RateLimiter::clear($throttleKey);
        session()->regenerate();

        $user = Auth::user();

        // Chưa xác minh email → đẩy sang trang chờ verify (nếu admin còn bật yêu cầu)
        if (Setting::get('email_verification_required', '1') !== '0' && !$user->hasVerifiedEmail()) {
            $this->redirect(route('verification.notice'), navigate: true);
            return;
        }

        // Check membership status
        $membership = $user->membership;
        if (!$membership || $membership->status === 'expired') {
            $this->redirect(route('membership.expired'), navigate: true);
            return;
        }
        if ($membership->status === 'banned') {
            Auth::logout();
            $this->error = 'Tài khoản của bạn đã bị khóa.';
            return;
        }

        // If no class selected, go to onboarding
        if (!$user->class) {
            $this->redirect(route('onboarding'), navigate: true);
            return;
        }

        $this->redirect(route('feed'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login-form')
            ->layout('layouts.guest', ['title' => 'Đăng nhập — ' . brand()->name]);
    }
}
