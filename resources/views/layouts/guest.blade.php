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
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-8" style="background:#F7FAFC;">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
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
