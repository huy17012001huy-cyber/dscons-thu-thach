<div>
    <div class="card auth-card">
        <div class="mb-6">
            <span class="badge" style="background:#E1F4F7;color:#125A96;">Bảo mật tài khoản</span>
            <h1 class="mt-3" style="font-size:1.5rem;font-weight:800;">Đặt lại mật khẩu</h1>
            <p style="font-size:.875rem;margin-top:.4rem;">Chọn mật khẩu mới an toàn cho tài khoản của bạn.</p>
        </div>

        @if($error)
            <div class="auth-notice auth-notice-error mb-4" role="alert">{{ $error }}</div>
        @endif

        <form wire:submit="submit" class="flex flex-col gap-4">
            <div>
                <label for="reset-email" class="auth-label">Email</label>
                <input id="reset-email" wire:model="email" type="email" class="input" placeholder="ban@email.com" autocomplete="email"
                    @error('email') aria-invalid="true" aria-describedby="reset-email-error" @enderror>
                @error('email') <p id="reset-email-error" class="auth-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div x-data="{ show: false }">
                <label for="reset-password" class="auth-label">Mật khẩu mới</label>
                <div style="position:relative;">
                    <input id="reset-password" wire:model="password" :type="show ? 'text' : 'password'" class="input" placeholder="Ít nhất 8 ký tự" autocomplete="new-password" style="padding-right:2.75rem;"
                        @error('password') aria-invalid="true" aria-describedby="reset-password-error" @enderror>
                    <button type="button" @click="show = !show" :aria-pressed="show.toString()" :aria-label="show ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'"
                        style="position:absolute;right:.55rem;top:50%;transform:translateY(-50%);width:34px;height:34px;border:0;border-radius:8px;background:transparent;color:#61798A;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                        <svg x-show="!show" aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <svg x-show="show" aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 5.1A10.5 10.5 0 0112 5c4.48 0 8.27 2.94 9.54 7a10.7 10.7 0 01-2.22 3.64M6.2 6.2A10.7 10.7 0 002.46 12c1.27 4.06 5.06 7 9.54 7 1.03 0 2.02-.16 2.95-.45"/></svg>
                    </button>
                </div>
                @error('password') <p id="reset-password-error" class="auth-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="reset-password-confirmation" class="auth-label">Nhập lại mật khẩu mới</label>
                <input id="reset-password-confirmation" wire:model="password_confirmation" type="password" class="input" placeholder="••••••••" autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-primary w-full justify-center" wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit">Đặt lại mật khẩu</span>
                <span wire:loading wire:target="submit">Đang xử lý...</span>
            </button>
        </form>

        <p class="auth-secondary"><a href="{{ route('login') }}" wire:navigate>Quay lại đăng nhập</a></p>
    </div>
</div>
