<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#0F3D5E">
    <title>{{ $title ?? brand()->name }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('dscons-ai-bim-lab.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-8" style="background:#F4F7F9;">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-3 text-2xl font-extrabold tracking-tight" style="color:#0F3D5E;">
                <img src="{{ asset('dscons-ai-bim-lab.svg') }}" alt="" style="width:42px;height:42px;">
                {{ brand()->name }}
            </a>
            <p class="text-sm mt-2" style="color:#61798A;">Chuyển hóa kỹ sư MEP bằng AI &amp; Vibe Coding</p>
        </div>

        {{ $slot }}
    </div>
    @livewireScripts
</body>
</html>
