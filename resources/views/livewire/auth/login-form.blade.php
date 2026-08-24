<div class="login-form">
    <style>
        .login-form .card { padding: clamp(20px, 5vw, 30px); }
        .login-form .login-alert { border-radius: 12px; line-height: 1.5; }
        .login-form .google-login-button { min-height: 48px; }
        .login-form .google-mark { display: inline-grid; place-items: center; width: 24px; height: 24px; border-radius: 50%; background: #fff; color: #4285f4; font-weight: 800; font-size: 1rem; }
    </style>

    <div class="card">
        <h1 style="font-size:1.5rem; font-weight:700; color:#1A1A1A; margin-bottom:0.25rem;">Đăng nhập</h1>
        <p style="color:#5C5C66; font-size:0.875rem; margin-bottom:1.5rem;">Chào mừng bạn trở lại</p>

        @if(session('status'))
            <div class="login-alert" role="status" style="background:#DCFCE7; border:1px solid #BBF7D0; color:#166534; padding:0.75rem; margin-bottom:1rem;">
                {{ session('status') }}
            </div>
        @endif

        @if(session('error') || $error)
            <div class="login-alert" role="alert" style="background:#FEE2E2; border:1px solid #FECACA; color:#991B1B; padding:0.75rem; margin-bottom:1rem;">
                {{ session('error') ?: $error }}
            </div>
        @endif

        @if(config('auth.mode') === 'password')
            <div x-data x-init="
                (async () => {
                    if (!window.crypto?.subtle) return;
                    const parts = [screen.width + 'x' + screen.height, Intl.DateTimeFormat().resolvedOptions().timeZone, navigator.userAgent];
                    const buf = new TextEncoder().encode(parts.join('|'));
                    const hash = await crypto.subtle.digest('SHA-256', buf);
                    const hex = Array.from(new Uint8Array(hash)).slice(0, 8).map(b => b.toString(16).padStart(2, '0')).join('');
                    $wire.set('fingerprint', hex, false);
                })();
            "></div>

            <form wire:submit="login" class="flex flex-col gap-4">
                <div>
                    <label for="login-email" style="display:block; font-size:0.8rem; font-weight:600; color:#2E2E2E; margin-bottom:0.375rem;">Email</label>
                    <input id="login-email" wire:model="email" type="email" class="input" placeholder="ban@email.com" autocomplete="email" autofocus
                        @error('email') aria-invalid="true" aria-describedby="login-email-error" @enderror>
                    @error('email') <p id="login-email-error" role="alert" style="color:#991B1B; font-size:0.75rem; margin-top:0.25rem;">{{ $message }}</p> @enderror
                </div>

                <div x-data="{ show: false }">
                    <label for="login-password" style="display:block; font-size:0.8rem; font-weight:600; color:#2E2E2E; margin-bottom:0.375rem;">Mật khẩu</label>
                    <div style="position:relative;">
                        <input id="login-password" wire:model="password" :type="show ? 'text' : 'password'" class="input" placeholder="••••••••" autocomplete="current-password" style="padding-right:2.5rem;"
                            @error('password') aria-invalid="true" aria-describedby="login-password-error" @enderror>
                        <button type="button" @click="show = !show" :aria-label="show ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'"
                            style="position:absolute; right:0.625rem; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#5C5C66; padding:0.25rem; display:flex; align-items:center;">
                            <span aria-hidden="true">◉</span>
                        </button>
                    </div>
                    @error('password') <p id="login-password-error" role="alert" style="color:#991B1B; font-size:0.75rem; margin-top:0.25rem;">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2" style="font-size:0.8rem; color:#5C5C66; cursor:pointer;">
                        <input wire:model="remember" type="checkbox" style="accent-color:#d17856;">
                        Nhớ đăng nhập
                    </label>
                    <a href="{{ route('password.request') }}" wire:navigate style="font-size:0.8rem; color:#d17856; font-weight:600;">Quên mật khẩu?</a>
                </div>

                <button type="submit" class="btn btn-primary w-full justify-center" wire:loading.attr="disabled" wire:target="login">
                    <span wire:loading.remove wire:target="login">Đăng nhập</span>
                    <span wire:loading wire:target="login">Đang xử lý...</span>
                </button>
            </form>
        @else
            <a href="{{ route('auth.google.redirect', $redirectTo ? ['redirect' => $redirectTo] : []) }}" class="btn btn-primary google-login-button w-full justify-center" rel="nofollow">
                <span class="google-mark" aria-hidden="true">G</span>
                <span>Đăng nhập bằng Google</span>
            </a>

            <p style="text-align:center; margin-top:1.25rem; font-size:0.8rem; color:#5C5C66; line-height:1.5;">
                Bạn sẽ được chuyển tới Google để xác thực an toàn. Hệ thống không lưu mật khẩu Google của bạn.
            </p>
        @endif
    </div>
</div>
