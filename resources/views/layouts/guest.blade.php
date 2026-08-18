<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>{{ $title ?? 'The All In Plan™' }}</title>
    <link rel="icon" type="image/webp" href="{{ asset('favicon.webp') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-8" style="background:#F7F5F3;">
    <div class="w-full max-w-md">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="/" class="text-2xl font-extrabold tracking-tight" style="color:#1A1A1A;">
                THE ALL IN <span style="color:#d17856;">PLAN</span>™
            </a>
            <p class="text-sm mt-1" style="color:#5C5C66;">Công Thức Kiếm Tiền</p>
        </div>

        {{ $slot }}
    </div>
    @livewireScripts
</body>
</html>
