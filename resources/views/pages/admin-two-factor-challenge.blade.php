<x-layouts.app>
    <section class="card" style="max-width:480px;margin:5rem auto;border:1px solid #9DC9E2;">
        <p style="font-size:.75rem;font-weight:800;letter-spacing:.08em;color:#1F77BE;text-transform:uppercase;">Bảo mật Super Admin</p>
        <h1 style="margin:.35rem 0 .6rem;font-size:1.35rem;color:#123B59;">Xác thực hai lớp</h1>
        <p style="margin:0 0 1rem;color:#456273;font-size:.9rem;line-height:1.55;">Nhập mã 6 số từ ứng dụng xác thực hoặc một recovery code để tiếp tục.</p>
        <form method="post" action="{{ auth()->check() ? route('admin.two-factor.verify.current') : route('admin.two-factor.verify.submit') }}" style="display:grid;gap:.75rem;">
            @csrf
            <label>Mã xác thực<input class="input" name="code" inputmode="numeric" autocomplete="one-time-code"></label>
            <label>hoặc recovery code<input class="input" name="recovery_code" autocomplete="one-time-code"></label>
            @error('code')<p style="margin:0;color:#B42318;font-size:.8rem;">{{ $message }}</p>@enderror
            <button class="btn btn-primary" type="submit">Xác thực và tiếp tục</button>
        </form>
    </section>
</x-layouts.app>
