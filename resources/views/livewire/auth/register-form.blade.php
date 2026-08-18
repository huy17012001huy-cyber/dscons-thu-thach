<div>
    <div class="card">
        <h1 style="font-size:1.5rem; font-weight:700; color:#1A1A1A; margin-bottom:0.25rem;">Tham gia cộng đồng</h1>
        <p style="color:#5C5C66; font-size:0.875rem; margin-bottom:1.5rem;">3 ngày dùng thử miễn phí 🎁</p>

        {{-- Fingerprint client-side — xem giải thích trong login-form.blade.php --}}
        <div x-data x-init="
            (async () => {
                if (!window.crypto?.subtle) return;
                const parts = [
                    screen.width + 'x' + screen.height + 'x' + screen.colorDepth,
                    Intl.DateTimeFormat().resolvedOptions().timeZone,
                    new Date().getTimezoneOffset(),
                    navigator.language,
                    navigator.platform,
                    navigator.hardwareConcurrency || '',
                    navigator.deviceMemory || '',
                    navigator.userAgent,
                ];
                try {
                    const c = document.createElement('canvas');
                    const ctx = c.getContext('2d');
                    ctx.textBaseline = 'top';
                    ctx.font = '14px Arial';
                    ctx.fillStyle = '#f60'; ctx.fillRect(125, 1, 62, 20);
                    ctx.fillStyle = '#069'; ctx.fillText('taip-fp 🔐', 2, 15);
                    parts.push(c.toDataURL());
                } catch (e) {}
                try {
                    const c = document.createElement('canvas');
                    const gl = c.getContext('webgl') || c.getContext('experimental-webgl');
                    const ext = gl?.getExtension('WEBGL_debug_renderer_info');
                    if (ext) {
                        parts.push(gl.getParameter(ext.UNMASKED_VENDOR_WEBGL));
                        parts.push(gl.getParameter(ext.UNMASKED_RENDERER_WEBGL));
                    }
                } catch (e) {}
                const buf = new TextEncoder().encode(parts.join('|'));
                const h = await crypto.subtle.digest('SHA-256', buf);
                const hex = Array.from(new Uint8Array(h)).slice(0,8).map(b => b.toString(16).padStart(2,'0')).join('');
                $wire.set('fingerprint', hex, false);
            })();
        "></div>

        <form wire:submit="register" class="flex flex-col gap-4">
            <div>
                <label style="display:block; font-size:0.8rem; font-weight:600; color:#2E2E2E; margin-bottom:0.375rem;">Họ và tên</label>
                <input wire:model="name" type="text" class="input" placeholder="Nguyễn Văn A" autofocus>
                @error('name') <p style="color:#991B1B; font-size:0.75rem; margin-top:0.25rem;">{{ $message }}</p> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.8rem; font-weight:600; color:#2E2E2E; margin-bottom:0.375rem;">Email</label>
                <input wire:model="email" type="email" class="input" placeholder="ban@email.com">
                @error('email') <p style="color:#991B1B; font-size:0.75rem; margin-top:0.25rem;">{{ $message }}</p> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.8rem; font-weight:600; color:#2E2E2E; margin-bottom:0.375rem;">Mật khẩu</label>
                <input wire:model="password" type="password" class="input" placeholder="Tối thiểu 8 ký tự">
                @error('password') <p style="color:#991B1B; font-size:0.75rem; margin-top:0.25rem;">{{ $message }}</p> @enderror
            </div>

            <div>
                <label style="display:block; font-size:0.8rem; font-weight:600; color:#2E2E2E; margin-bottom:0.375rem;">Xác nhận mật khẩu</label>
                <input wire:model="password_confirmation" type="password" class="input" placeholder="Nhập lại mật khẩu">
            </div>

            <button type="submit" class="btn btn-primary w-full justify-center" wire:loading.attr="disabled">
                <span wire:loading.remove>Bắt đầu dùng thử miễn phí</span>
                <span wire:loading>Đang tạo tài khoản...</span>
            </button>
        </form>

        <p style="text-align:center; margin-top:1.25rem; font-size:0.75rem; color:#5C5C66;">
            Khi đăng ký, bạn đồng ý với Điều khoản sử dụng của The All In Plan™
        </p>

        <p style="text-align:center; margin-top:0.75rem; font-size:0.875rem; color:#5C5C66;">
            Đã có tài khoản?
            <a href="{{ route('login') }}" style="color:#d17856; font-weight:600;">Đăng nhập</a>
        </p>
    </div>
</div>
