<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#1F77BE">
    <title>Không tìm thấy trang — DSCons</title>
    <link rel="icon" type="image/png" href="{{ asset('1024x1024-da xoa nen.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        .error-page { display: grid; min-height: 100vh; place-items: center; margin: 0; padding: 24px 16px; background: #F5F9FC; color: #123B59; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        .error-page-content { width: min(100%, 560px); text-align: center; }
        .error-page-brand { display: inline-flex; align-items: center; gap: 10px; margin-bottom: 26px; color: #123B59; font-size: 16px; font-weight: 800; text-decoration: none; }
        .error-page-brand img { width: 38px; height: 38px; padding: 3px; border: 1px solid #D7E5EE; border-radius: 10px; background: #E1F4F7; object-fit: contain; }
        .error-page-card { padding: clamp(28px, 7vw, 48px); border: 1px solid #D7E5EE; border-radius: 18px; background: #fff; box-shadow: 0 8px 24px rgba(18,59,89,.08); }
        .error-page-code { margin: 0; color: #1F77BE; font-size: clamp(56px, 16vw, 96px); font-weight: 800; letter-spacing: -.08em; line-height: .9; }
        .error-page-card h1 { margin: 20px 0 0; color: #123B59; font-size: clamp(24px, 5vw, 30px); font-weight: 800; letter-spacing: -.03em; line-height: 1.2; }
        .error-page-card > p:not(.error-page-code) { max-width: 390px; margin: 12px auto 0; color: #61798A; font-size: 14px; line-height: 1.65; }
        .error-page-actions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-top: 28px; }
        .error-page-actions .btn { min-height: 44px; justify-content: center; text-decoration: none; }
        .error-page-actions .btn:focus-visible { outline: 3px solid rgba(31,119,190,.24); outline-offset: 3px; }
        @media (max-width: 420px) { .error-page-actions { grid-template-columns: 1fr; } }
    </style>
</head>
<body class="error-page">
    <main class="error-page-content" aria-labelledby="error-page-heading">
        <a href="{{ route('communities') }}" class="error-page-brand"><img src="{{ asset('1024x1024-da xoa nen.png') }}" alt="">DSCons Việt Nam</a>
        <section class="error-page-card">
            <p class="error-page-code" aria-hidden="true">404</p>
            <h1 id="error-page-heading">Không tìm thấy trang này</h1>
            <p>Đường dẫn có thể đã thay đổi hoặc trang không còn tồn tại. Bạn có thể quay lại để tiếp tục khám phá và học tập.</p>
            <div class="error-page-actions">
                <a href="{{ route('communities') }}" class="btn btn-secondary">Khám phá cộng đồng</a>
                <a href="{{ auth()->check() ? route('feed') : route('login') }}" class="btn btn-primary">{{ auth()->check() ? 'Về bảng tin' : 'Đăng nhập' }}</a>
            </div>
        </section>
    </main>
</body>
</html>
