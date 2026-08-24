<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#1F77BE">
    <title>{{ $title ?? brand()->name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('1024x1024-da xoa nen.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        .guest-shell { background: #F7FAFC; }
        .guest-shell .guest-brand { text-wrap: balance; }
        .guest-shell .card { border-radius: 20px; border-color: #D7E5EA; box-shadow: 0 16px 36px rgba(18,59,89,.10); }
        @media (max-width: 640px) { .guest-shell { padding: 20px 16px !important; align-items: flex-start !important; padding-top: 48px !important; } .guest-shell .guest-brand { margin-bottom: 32px !important; } }
    </style>
</head>
<body class="guest-shell min-h-screen flex flex-col items-center justify-center p-8">
    <div class="w-full max-w-md">
        <div class="guest-brand text-center mb-8">
            <a href="/" class="inline-flex items-center" aria-label="{{ brand()->name }}">
                <img src="{{ asset('dscons-logo.png') }}" alt="DSCons — Design, Solution & Construction" style="width:260px;max-width:100%;height:auto;">
            </a>
            <p class="text-sm mt-3" style="color:#61798A;">Nền tảng học tập, thử thách và cộng đồng dành cho kỹ sư MEP</p>
        </div>

        {{ $slot }}
    </div>
    @livewireScripts
</body>
</html>
