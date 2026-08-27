<x-layouts.app>
    <section class="card" style="max-width:780px;margin:2rem auto;border:1px solid #9DC9E2;">
        <p style="font-size:.75rem;font-weight:800;letter-spacing:.08em;color:#1F77BE;text-transform:uppercase;">Bảo mật Super Admin</p>
        <h1 style="margin:.35rem 0 .6rem;font-size:1.35rem;color:#123B59;">Xác thực hai lớp và phiên đăng nhập</h1>
        @if(session('success'))<p style="padding:.7rem .8rem;border-radius:.6rem;background:#EAF7F0;color:#166534;">{{ session('success') }}</p>@endif

        @if($user->two_factor_confirmed_at)
            <p style="padding:.8rem;border:1px solid #B9E1CB;border-radius:.65rem;background:#F1FBF5;color:#166534;">Đã bật 2FA từ {{ $user->two_factor_confirmed_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}.</p>
        @elseif(!is_array($setup))
            <form method="post" action="{{ route('admin.security.prepare') }}">@csrf <button class="btn btn-primary">Tạo mã thiết lập 2FA</button></form>
        @endif

        @if(is_array($setup))
            <div style="margin-top:1rem;padding:1rem;border:1px solid #CFE3ED;border-radius:.75rem;background:#F7FCFF;">
                <p style="margin:0 0 .6rem;color:#29485B;">Mở ứng dụng Authenticator, chọn thêm tài khoản thủ công và dán mã bên dưới. Sau đó nhập mã 6 số để xác nhận.</p>
                <textarea class="input" rows="3" readonly>{{ $setup['otp_auth_url'] }}</textarea>
                <p style="font-size:.78rem;color:#456273;">Recovery code — chỉ hiển thị lần này:</p>
                <p style="font-family:monospace;line-height:1.8;color:#123B59;">{{ implode(' · ', $setup['recovery_codes']) }}</p>
                <form method="post" action="{{ route('admin.security.confirm') }}" style="display:flex;gap:.6rem;align-items:end;">@csrf <label style="flex:1;">Mã 6 số<input class="input" name="code" inputmode="numeric" autocomplete="one-time-code"></label><button class="btn btn-primary">Xác nhận</button></form>
                @error('code')<p style="color:#B42318;font-size:.8rem;">{{ $message }}</p>@enderror
            </div>
        @endif

        <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid #D8E5EA;">
            <h2 style="font-size:1rem;color:#123B59;">Phiên đăng nhập</h2>
            <p style="font-size:.85rem;color:#456273;">{{ $sessions->count() }} phiên đang được lưu. Thu hồi các phiên khác khi bạn nghi ngờ tài khoản đã được truy cập.</p>
            <form method="post" action="{{ route('admin.security.sessions.revoke') }}">@csrf<button class="btn" style="border-color:#F3B4B4;color:#B42318;background:#FFF6F6;">Thu hồi các phiên khác</button></form>
        </div>
    </section>
</x-layouts.app>
