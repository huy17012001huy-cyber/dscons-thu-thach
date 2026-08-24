<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#1F77BE">
    <title>Membership hết hạn — DSCons</title>
    <link rel="icon" type="image/png" href="{{ asset('1024x1024-da xoa nen.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        .membership-expired-page { min-height: 100vh; margin: 0; padding: 24px 16px; background: #F5F9FC; color: #123B59; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        .membership-expired-shell { display: grid; min-height: calc(100vh - 48px); place-items: center; }
        .membership-expired-content { width: min(100%, 520px); }
        .membership-expired-brand { display: flex; justify-content: center; margin-bottom: 22px; }
        .membership-expired-brand img { width: 172px; max-width: 72%; height: auto; }
        .membership-expired-card { padding: clamp(24px, 6vw, 40px); border: 1px solid #D7E5EE; border-radius: 18px; background: #fff; box-shadow: 0 8px 24px rgba(18,59,89,.08); text-align: center; }
        .membership-expired-icon { display: grid; width: 52px; height: 52px; place-items: center; margin: 0 auto 18px; border: 1px solid #F1C67B; border-radius: 14px; background: #FFF1D6; color: #A85A00; }
        .membership-expired-icon svg { width: 27px; height: 27px; }
        .membership-expired-card h1 { margin: 0; color: #123B59; font-size: clamp(24px, 5vw, 30px); font-weight: 800; letter-spacing: -.03em; line-height: 1.2; }
        .membership-expired-card p { max-width: 390px; margin: 12px auto 0; color: #61798A; font-size: 14px; line-height: 1.65; }
        .membership-expired-actions { display: grid; gap: 10px; margin-top: 26px; }
        .membership-expired-actions .btn { min-height: 44px; justify-content: center; text-decoration: none; }
        .membership-expired-actions .btn:focus-visible { outline: 3px solid rgba(31,119,190,.24); outline-offset: 3px; }
    </style>
</head>
<body class="membership-expired-page">
    <main class="membership-expired-shell">
        <div class="membership-expired-content">
            <div class="membership-expired-brand"><img src="{{ asset('dscons-logo.png') }}" alt="DSCons Việt Nam"></div>
            <section class="membership-expired-card" aria-labelledby="membership-expired-heading">
                <div class="membership-expired-icon" aria-hidden="true"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01M10.3 3.5h3.4L21 17.2a1.5 1.5 0 01-1.3 2.3H4.3A1.5 1.5 0 013 17.2L10.3 3.5z"/></svg></div>
                <h1 id="membership-expired-heading">Membership đã hết hạn</h1>
                <p>Gia hạn để tiếp tục truy cập khóa học, Challenge và các hoạt động học tập trong cộng đồng DSCons.</p>
                <div class="membership-expired-actions">
                    <a href="{{ route('membership.pricing') }}" class="btn btn-primary">Xem gói và gia hạn</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost" style="width:100%;">Đăng xuất</button>
                    </form>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
