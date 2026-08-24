<div>
    <style>
        /* Keep the secondary auth action balanced even if the shared stylesheet is cached. */
        .auth-card .auth-secondary {
            display: flex !important;
            width: 100%;
            box-sizing: border-box;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: .25rem;
            margin: 1.25rem 0 0;
            padding-top: 1rem;
            border-top: 1px solid var(--ds-border);
            line-height: 1.45;
            text-align: center !important;
        }
    </style>
    <div class="card auth-card">
        <div class="mb-6">
            <span class="badge" style="background:#E1F4F7;color:#125A96;">Khôi phục tài khoản</span>
            <h1 class="mt-3" style="font-size:1.5rem;font-weight:800;">Quên mật khẩu?</h1>
            <p style="font-size:.875rem;margin-top:.4rem;">Nhập email của bạn, chúng tôi sẽ gửi liên kết để đặt lại mật khẩu.</p>
        </div>

        @if($sent)
            <div class="auth-notice auth-notice-success" role="status">
                <span aria-hidden="true">✓</span>
                <span>Nếu email này có tài khoản, chúng tôi đã gửi một liên kết đặt lại mật khẩu. Hãy kiểm tra cả mục Spam/Quảng cáo.</span>
            </div>
            <a href="{{ route('login') }}" wire:navigate class="btn btn-primary w-full justify-center mt-5">Quay lại đăng nhập</a>
        @else
            @if($error)
                <div class="auth-notice auth-notice-error mb-4" role="alert">{{ $error }}</div>
            @endif

            <form wire:submit.prevent="submit" class="flex flex-col gap-4">
                <div>
                    <label for="forgot-email" class="auth-label">Email</label>
                    <input id="forgot-email" wire:model="email" type="email" class="input" placeholder="ban@email.com" autocomplete="email" autofocus
                        @error('email') aria-invalid="true" aria-describedby="forgot-email-error" @enderror>
                    @error('email') <p id="forgot-email-error" class="auth-error" role="alert">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="btn btn-primary w-full justify-center" wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">Gửi liên kết đặt lại</span>
                    <span wire:loading wire:target="submit">Đang gửi...</span>
                </button>
            </form>

            <p class="auth-secondary"><span>Đã nhớ mật khẩu?</span>
                <a href="{{ route('login') }}" wire:navigate>Đăng nhập</a>
            </p>
        @endif
    </div>
</div>
