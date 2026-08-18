<div>
    <div class="card">
        <div style="font-size:2rem; text-align:center; margin-bottom:0.5rem;">📩</div>
        <h1 style="font-size:1.5rem; font-weight:700; color:#1A1A1A; margin-bottom:0.25rem; text-align:center;">Xác minh email</h1>
        <p style="color:#5C5C66; font-size:0.875rem; margin-bottom:1.25rem; text-align:center;">
            Chúng tôi vừa gửi một liên kết xác minh tới<br>
            <strong style="color:#1A1A1A;">{{ auth()->user()->email }}</strong>
        </p>

        @if($sent)
        <div style="background:#DCFCE7; border:1px solid #BBF7D0; color:#166534; padding:0.75rem; border-radius:0.5rem; font-size:0.875rem; margin-bottom:1rem;">
            ✓ Đã gửi lại email xác minh. Kiểm tra hộp thư (và cả mục Spam/Quảng cáo) nhé.
        </div>
        @endif

        @error('resend')
        <div style="background:#FEE2E2; border:1px solid #FECACA; color:#991B1B; padding:0.75rem; border-radius:0.5rem; font-size:0.875rem; margin-bottom:1rem;">
            {{ $message }}
        </div>
        @enderror

        <p style="color:#5C5C66; font-size:0.8rem; line-height:1.5; margin-bottom:1.25rem;">
            Mở email đó và bấm nút xác minh để vào cộng đồng. Chưa thấy email? Đợi vài phút rồi gửi lại bên dưới.
        </p>

        <button wire:click="resend" class="btn btn-primary w-full justify-center" wire:loading.attr="disabled" wire:target="resend">
            <span wire:loading.remove wire:target="resend">Gửi lại email xác minh</span>
            <span wire:loading wire:target="resend">Đang gửi...</span>
        </button>

        <p style="text-align:center; margin-top:1.25rem; font-size:0.875rem; color:#5C5C66;">
            Sai email?
            <button wire:click="logout" type="button" style="color:#d17856; font-weight:600; background:none; border:none; cursor:pointer; padding:0;">Đăng xuất</button>
            để đăng nhập lại.
        </p>
    </div>
</div>
