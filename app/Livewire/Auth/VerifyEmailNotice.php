<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class VerifyEmailNotice extends Component
{
    public bool $sent = false;

    public function mount(): void
    {
        // Đã verify rồi thì cho vào thẳng
        if (Auth::user()?->hasVerifiedEmail()) {
            $this->redirect(route('feed'), navigate: true);
        }
    }

    public function resend(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirect(route('feed'), navigate: true);
            return;
        }

        $key = 'verify-resend|' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('resend', 'Vui lòng đợi ' . $seconds . ' giây trước khi gửi lại.');
            return;
        }
        RateLimiter::hit($key, 60);

        $user->sendEmailVerificationNotification();
        $this->sent = true;
    }

    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        $this->redirect(route('login'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.verify-email-notice')
            ->layout('layouts.guest', ['title' => 'Xác minh email — ' . brand()->name]);
    }
}
