<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#2563EB">
    <title>{{ $title ?? brand()->name }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-8" style="background:#F8FAFC;">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="/" class="text-2xl font-extrabold tracking-tight" style="color:#0F172A;">
                {{ brand()->name }}
            </a>
            <p class="text-sm mt-1" style="color:#64748B;">Học tập, thử thách và cùng tiến bộ</p>
        </div>

        {{ $slot }}
    </div>
    @livewireScripts
</body>
</html>
