<?php

namespace App\Livewire\Auth;

use App\Models\Membership;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

    // Hash truncated SHA-256 client-side tính từ canvas+webgl+screen+navigator.
    // Set bởi inline JS trong view trước khi user nhấn nút Đăng ký.
    public string $fingerprint = '';

    public function mount(): void
    {
        if (brand()->registration_mode !== 'open') {
            $this->redirect(route('login'), navigate: true);
        }
    }

    public function register(): void
    {
        // Block registration on invite-only or closed brands
        if (brand()->registration_mode !== 'open') {
            $this->addError('email', 'Cộng đồng này chỉ dành cho thành viên được mời.');

            return;
        }

        $this->validate();

        $throttleKey = 'register|'.request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', 'Vui lòng đợi '.$seconds.' giây trước khi đăng ký tài khoản mới.');

            return;
        }

        // Transliterate Vietnamese → ASCII before creating username
        $ascii = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', trim($this->name)) ?: '';
        $username = preg_replace('/\s+/', '.', $ascii) ?: '';
        $username = preg_replace('/[^a-z0-9._]/', '', $username) ?: '';
        $base = $username;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base.$i++;
        }

        $referrer = null;
        $refUsername = session('referral');
        if ($refUsername) {
            $referrer = User::where('username', $refUsername)->first();
        }

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'username' => $username,
            'password' => Hash::make($this->password),
            'level' => 1,
            'xp' => 0,
            'aip' => 0,
            'streak' => 0,
            'referred_by' => $referrer?->id,
        ]);

        // Create active membership
        Membership::create([
            'user_id' => $user->id,
            'status' => 'active',
            'plan' => 'lifetime',
            'expires_at' => '2099-12-31',
            'referred_by' => $referrer?->id,
        ]);
        $user->brandRoles()->syncWithoutDetaching([
            brand()->id => ['role' => 'member'],
        ]);

        session()->forget('referral');

        // Đẩy fingerprint vào session để RecordLoginLog listener đọc khi Login event fire
        if ($this->fingerprint !== '') {
            session()->put('_login_fp', $this->fingerprint);
        }

        Auth::login($user);
        session()->regenerate();
        RateLimiter::hit($throttleKey, 3600); // Max 3 registrations per hour per IP

        // Nếu admin tắt yêu cầu verify → đánh dấu đã verify, vào thẳng feed
        if (Setting::get('email_verification_required', '1') === '0') {
            $user->markEmailAsVerified();
            $this->redirect(route('feed'), navigate: true);

            return;
        }

        // Gửi email xác minh — chưa cho vào feed cho tới khi verify
        $user->sendEmailVerificationNotification();
        $this->redirect(route('verification.notice'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.register-form')
            ->layout('layouts.guest', ['title' => 'Đăng ký — '.brand()->name]);
    }
}
