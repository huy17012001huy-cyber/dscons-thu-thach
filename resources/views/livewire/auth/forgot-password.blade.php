<div>
    <div class="card">
        <h1 style="font-size:1.5rem; font-weight:700; color:#1A1A1A; margin-bottom:0.25rem;">Quên mật khẩu</h1>
        <p style="color:#5C5C66; font-size:0.875rem; margin-bottom:1.5rem;">Nhập email của bạn, chúng tôi sẽ gửi liên kết để đặt lại mật khẩu.</p>

        @if($sent)
        <div style="background:#DCFCE7; border:1px solid #BBF7D0; color:#166534; padding:0.75rem; border-radius:0.5rem; font-size:0.875rem; margin-bottom:1rem;">
            ✓ Nếu email này có tài khoản, chúng tôi đã gửi một liên kết đặt lại mật khẩu. Kiểm tra hộp thư (và cả mục Spam/Quảng cáo) nhé.
        </div>
        <a href="{{ route('login') }}" wire:navigate class="btn btn-primary w-full justify-center">Quay lại đăng nhập</a>
        @else
            @if($error)
            <div style="background:#FEE2E2; border:1px solid #FECACA; color:#991B1B; padding:0.75rem; border-radius:0.5rem; font-size:0.875rem; margin-bottom:1rem;">
                {{ $error }}
            </div>
            @endif

            <form wire:submit="submit" class="flex flex-col gap-4">
                <div>
                    <label style="display:block; font-size:0.8rem; font-weight:600; color:#2E2E2E; margin-bottom:0.375rem;">Email</label>
                    <input wire:model="email" type="email" class="input" placeholder="ban@email.com" autofocus>
                    @error('email') <p style="color:#991B1B; font-size:0.75rem; margin-top:0.25rem;">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="btn btn-primary w-full justify-center" wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">Gửi liên kết đặt lại</span>
                    <span wire:loading wire:target="submit">Đang gửi...</span>
                </button>
            </form>

            <p style="text-align:center; margin-top:1.25rem; font-size:0.875rem; color:#5C5C66;">
                Nhớ ra mật khẩu rồi?
                <a href="{{ route('login') }}" wire:navigate style="color:#d17856; font-weight:600;">Đăng nhập</a>
            </p>
        @endif
    </div>
</div>
