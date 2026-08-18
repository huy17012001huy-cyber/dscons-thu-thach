<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Rule;
use Livewire\Component;

class ResetPassword extends Component
{
    #[Rule('required')]
    public string $token = '';

    #[Rule('required|email')]
    public string $email = '';

    #[Rule('required|min:8|confirmed')]
    public string $password = '';

    public string $password_confirmation = '';

    public string $error = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = (string) request()->query('email', '');
    }

    public function submit(): void
    {
        $this->validate();

        $key = 'reset-password|' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->error = 'Bạn đã thử quá nhiều lần. Vui lòng đợi ' . RateLimiter::availableIn($key) . ' giây.';
            return;
        }
        RateLimiter::hit($key, 60);

        $status = Password::reset(
            [
                'email'                 => $this->email,
                'password'              => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token'                 => $this->token,
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            RateLimiter::clear($key);
            session()->flash('status', 'Đặt lại mật khẩu thành công. Đăng nhập bằng mật khẩu mới nhé.');
            $this->redirect(route('login'), navigate: true);
            return;
        }

        // Gộp INVALID_USER và INVALID_TOKEN thành một thông báo để không lộ
        // email nào có tài khoản (email có thể sửa được trên form).
        $this->error = $status === Password::RESET_THROTTLED
            ? 'Bạn thao tác quá nhanh. Vui lòng đợi một lát rồi thử lại.'
            : 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn. Hãy yêu cầu liên kết mới.';
    }

    public function render()
    {
        return view('livewire.auth.reset-password')
            ->layout('layouts.guest', ['title' => 'Đặt lại mật khẩu — ' . brand()->name]);
    }
}
