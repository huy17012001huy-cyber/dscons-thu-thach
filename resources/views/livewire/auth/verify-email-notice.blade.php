<div>
    <div class="card auth-card">
        <div style="display:flex;justify-content:center;margin-bottom:1rem;">
            <span class="status-icon" aria-hidden="true"><svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7.5A2.5 2.5 0 015.5 5h13A2.5 2.5 0 0121 7.5v9a2.5 2.5 0 01-2.5 2.5h-13A2.5 2.5 0 013 16.5v-9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7l8 6 8-6"/></svg></span>
        </div>
        <h1 style="font-size:1.5rem;font-weight:800;text-align:center;">Xác minh email</h1>
        <p style="font-size:.875rem;margin:.5rem 0 1.25rem;text-align:center;">Chúng tôi vừa gửi một liên kết xác minh tới<br><strong style="color:var(--ds-text);">{{ auth()->user()->email }}</strong></p>

        @if($sent)
            <div class="auth-notice auth-notice-success mb-4" role="status"><span aria-hidden="true">✓</span><span>Đã gửi lại email xác minh. Hãy kiểm tra hộp thư và mục Spam/Quảng cáo.</span></div>
        @endif

        @error('resend')
            <div class="auth-notice auth-notice-error mb-4" role="alert">{{ $message }}</div>
        @enderror

        <p style="font-size:.82rem;margin-bottom:1.25rem;">Mở email và bấm nút xác minh để vào cộng đồng. Chưa thấy email? Đợi vài phút rồi gửi lại bên dưới.</p>

        <button wire:click="resend" class="btn btn-primary w-full justify-center" wire:loading.attr="disabled" wire:target="resend">
            <span wire:loading.remove wire:target="resend">Gửi lại email xác minh</span>
            <span wire:loading wire:target="resend">Đang gửi...</span>
        </button>

        <p class="auth-secondary">Sai email?
            <button wire:click="logout" type="button" style="border:0;background:transparent;padding:0;cursor:pointer;">Đăng xuất</button>
            để đăng nhập lại.
        </p>
    </div>
</div>
