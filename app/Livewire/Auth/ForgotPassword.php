<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Rule;
use Livewire\Component;

class ForgotPassword extends Component
{
    #[Rule('required|email')]
    public string $email = '';

    public bool $sent = false;

    public string $error = '';

    public function mount(): void
    {
        if (brand()->registration_mode !== 'open') {
            $this->redirect(route('login'), navigate: true);
        }
    }

    public function submit(): void
    {
        $this->validate();

        $key = 'forgot-password|'.Str::lower($this->email).'|'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->error = 'Bạn đã thử quá nhiều lần. Vui lòng đợi '.RateLimiter::availableIn($key).' giây.';

            return;
        }
        RateLimiter::hit($key, 60);

        try {
            Password::sendResetLink(['email' => $this->email]);
        } catch (\Throwable $e) {
            // Gửi lỗi (vd. Resend chưa cấu hình) → ghi log để admin kiểm tra ở mục
            // "Gửi email thử". KHÔNG báo lỗi ra giao diện, nếu không sẽ lộ email nào
            // có tài khoản (email lạ trả về thành công, email thật mới ném lỗi gửi).
            report($e);
        }

        // Luôn báo thành công dù email có tồn tại hay không (chống dò email).
        $this->sent = true;
    }

    public function render(): View
    {
        return view('livewire.auth.forgot-password')
            ->layout('layouts.guest', ['title' => 'Quên mật khẩu — '.brand()->name]);
    }
}
