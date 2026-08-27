<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Core\Auth\UserProvisioningService;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Attributes\Rule;
use Livewire\Component;

class RegisterForm extends Component
{
    #[Rule('required|min:2|max:50')]
    public string $name = '';

    #[Rule('required|email|unique:users,email')]
    public string $email = '';

    #[Rule('required|min:8|confirmed')]
    public string $password = '';

    public string $password_confirmation = '';

    public string $fingerprint = '';

    public function mount(): void
    {
        if (brand()->registration_mode !== 'open') {
            $this->redirect(route('login'), navigate: true);
        }
    }

    public function register(): void
    {
        if (brand()->registration_mode !== 'open') {
            $this->addError('email', 'Cộng đồng này chỉ dành cho thành viên được mời.');

            return;
        }

        $this->validate();
        $throttleKey = 'register|'.request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $this->addError('email', 'Vui lòng đợi '.RateLimiter::availableIn($throttleKey).' giây trước khi đăng ký tài khoản mới.');

            return;
        }

        $referrer = null;
        $refUsername = session('referral');
        if ($refUsername) {
            $referrer = User::query()->where('username', $refUsername)->first();
        }
        $user = app(UserProvisioningService::class)->provisionPasswordMember(
            brand(),
            $this->name,
            $this->email,
            $this->password,
            $referrer,
        );

        session()->forget('referral');
        if ($this->fingerprint !== '') {
            session()->put('_login_fp', $this->fingerprint);
        }

        Auth::login($user);
        session()->regenerate();
        RateLimiter::hit($throttleKey, 3600);

        if (Setting::get('email_verification_required', '1') === '0') {
            $user->markEmailAsVerified();
            $this->redirect(route('feed'), navigate: true);

            return;
        }

        $user->sendEmailVerificationNotification();
        $this->redirect(route('verification.notice'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.register-form')
            ->layout('layouts.guest', ['title' => 'Đăng ký — '.brand()->name]);
    }
}
