<div>
    <div class="card">
        <h1 style="font-size:1.5rem; font-weight:700; color:#1A1A1A; margin-bottom:0.25rem;">Đăng nhập</h1>
        <div style="font-size:.78rem;color:#1F77BE;font-weight:700;margin-bottom:.35rem;">{{ brand()->name }}</div>
        <p style="color:#5C5C66; font-size:0.875rem; margin-bottom:1.5rem;">Chào mừng trở lại 👋</p>

        @if(session('status'))
        <div style="background:#DCFCE7; border:1px solid #BBF7D0; color:#166534; padding:0.75rem; border-radius:0.5rem; font-size:0.875rem; margin-bottom:1rem;">
            {{ session('status') }}
        </div>
        @endif

        @if($error)
        <div style="background:#FEE2E2; border:1px solid #FECACA; color:#991B1B; padding:0.75rem; border-radius:0.5rem; font-size:0.875rem; margin-bottom:1rem;">
            {{ $error }}
        </div>
        @endif

        {{-- Fingerprint client-side: tính hash từ canvas+webgl+screen+navigator
             rồi set vào property $fingerprint. Listener RecordLoginLog đọc qua session.
             Browser cũ/JS off thì property = "" → log fingerprint_hash = null (vô hại). --}}
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
                    ctx.fillStyle = '#069'; ctx.fillText('challenge-fp 🔐', 2, 15);
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
                $wire.set('fingerprint', hex, false); // false = không gọi server-render lại
            })();
        "></div>

        <form wire:submit="login" class="flex flex-col gap-4">
            <div>
                <label style="display:block; font-size:0.8rem; font-weight:600; color:#2E2E2E; margin-bottom:0.375rem;">Email</label>
                <input wire:model="email" type="email" class="input" placeholder="ban@email.com" autofocus>
                @error('email') <p style="color:#991B1B; font-size:0.75rem; margin-top:0.25rem;">{{ $message }}</p> @enderror
            </div>

            <div x-data="{ show: false }">
                <label style="display:block; font-size:0.8rem; font-weight:600; color:#2E2E2E; margin-bottom:0.375rem;">Mật khẩu</label>
                <div style="position:relative;">
                    <input wire:model="password" :type="show ? 'text' : 'password'" class="input" placeholder="••••••••" style="padding-right:2.5rem;">
                    <button type="button" @click="show = !show"
                        style="position:absolute; right:0.625rem; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#5C5C66; padding:0.25rem; display:flex; align-items:center;"
                        :aria-label="show ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'">
                        <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                @error('password') <p style="color:#991B1B; font-size:0.75rem; margin-top:0.25rem;">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2" style="font-size:0.8rem; color:#5C5C66; cursor:pointer;">
                    <input wire:model="remember" type="checkbox" style="accent-color:#d17856;">
                    Nhớ đăng nhập
                </label>
                <a href="{{ route('password.request') }}" wire:navigate style="font-size:0.8rem; color:#d17856; font-weight:600;">Quên mật khẩu?</a>
            </div>

            <button type="submit" class="btn btn-primary w-full justify-center" wire:loading.attr="disabled">
                <span wire:loading.remove>Đăng nhập</span>
                <span wire:loading>Đang xử lý...</span>
            </button>
        </form>

        <p style="text-align:center; margin-top:1.25rem; font-size:0.8rem; color:#8A8A94; line-height:1.5;">
            Tài khoản do ban tổ chức cấp. Nếu bạn gặp khó khăn khi đăng nhập,
            vui lòng liên hệ Admin và cung cấp email để được hỗ trợ.
        </p>
    </div>
</div>
