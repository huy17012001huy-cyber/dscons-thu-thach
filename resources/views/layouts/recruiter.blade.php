<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'DSCons Talent' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        :root { --talent-blue:#1F77BE; --talent-ink:#123B59; --talent-muted:#60798A; --talent-line:#D7E5EA; --talent-soft:#F1F8FB; --talent-orange:#F39402; }
        body.talent-body { margin:0; background:#F4F8F9; color:var(--talent-ink); }
        .talent-shell { min-height:100vh; }
        .talent-header { position:sticky;top:0;z-index:30;background:rgba(255,255,255,.96);backdrop-filter:blur(12px);border-bottom:1px solid var(--talent-line); }
        .talent-header-inner { max-width:1240px;margin:auto;min-height:70px;padding:.7rem 1.2rem;display:flex;align-items:center;gap:1rem; }
        .talent-brand { display:flex;align-items:center;gap:.65rem;color:var(--talent-ink);text-decoration:none;font-weight:900;letter-spacing:-.04em; }
        .talent-brand-mark { width:34px;height:34px;border-radius:10px;display:grid;place-items:center;background:var(--talent-blue);color:#fff;font-size:.78rem;box-shadow:0 5px 14px rgba(31,119,190,.22); }
        .talent-brand-accent { color:var(--talent-orange); }
        .talent-community { display:inline-flex;align-items:center;gap:.45rem;margin-left:.25rem;padding:.4rem .65rem;border-left:1px solid var(--talent-line);color:var(--talent-muted);font-size:.77rem;font-weight:700; }
        .talent-nav { display:flex;align-items:center;gap:.2rem;margin-left:auto;flex-wrap:wrap; }
        .talent-nav a { display:inline-flex;align-items:center;gap:.38rem;padding:.55rem .68rem;border-radius:9px;color:var(--talent-muted);text-decoration:none;font-size:.78rem;font-weight:750; }
        .talent-nav a:hover,.talent-nav a.active { background:var(--talent-soft);color:var(--talent-blue); }
        .talent-avatar { width:35px;height:35px;border-radius:50%;object-fit:cover;border:2px solid #DCECF3; }
        .talent-main { max-width:1240px;margin:auto;padding:1.25rem 1.2rem 4rem; }
        .talent-kicker { color:var(--talent-orange);font-size:.7rem;letter-spacing:.12em;text-transform:uppercase;font-weight:900; }
        .talent-heading { margin:.3rem 0 0;font-size:clamp(1.7rem,3vw,2.75rem);line-height:1.05;letter-spacing:-.055em;color:var(--talent-ink); }
        .talent-subtitle { color:var(--talent-muted);line-height:1.65;max-width:720px; }
        .talent-card { background:#fff;border:1px solid var(--talent-line);border-radius:16px;box-shadow:0 10px 26px rgba(18,59,89,.055); }
        .talent-btn { display:inline-flex;align-items:center;justify-content:center;gap:.42rem;min-height:42px;padding:.65rem .9rem;border-radius:10px;border:1px solid transparent;text-decoration:none;font:inherit;font-size:.8rem;font-weight:800;cursor:pointer;transition:.18s ease; }
        .talent-btn-primary { background:var(--talent-blue);color:#fff;box-shadow:0 7px 16px rgba(31,119,190,.2); }
        .talent-btn-primary:hover { background:#185f98;transform:translateY(-1px); }
        .talent-btn-secondary { background:#fff;color:var(--talent-blue);border-color:#BDD9E6; }
        .talent-btn-secondary:hover { background:var(--talent-soft); }
        .talent-chip { display:inline-flex;align-items:center;gap:.3rem;padding:.4rem .62rem;border:1px solid #CFE3ED;border-radius:999px;background:#fff;color:var(--talent-muted);font-size:.74rem;font-weight:750;cursor:pointer; }
        .talent-chip[aria-pressed="true"] { border-color:var(--talent-blue);background:#EAF5FB;color:var(--talent-blue); }
        .talent-input { width:100%;box-sizing:border-box;border:1px solid #C9DDE6;border-radius:10px;background:#fff;color:var(--talent-ink);padding:.7rem .8rem;font:inherit;font-size:.82rem; }
        .talent-input:focus { outline:0;border-color:var(--talent-blue);box-shadow:0 0 0 3px rgba(31,119,190,.13); }
        .talent-status { display:inline-flex;align-items:center;gap:.35rem;padding:.36rem .56rem;border-radius:999px;background:#EDF8F1;color:#18794E;font-size:.7rem;font-weight:800; }
        @media (max-width:760px) { .talent-header-inner{padding:.65rem .85rem}.talent-community{display:none}.talent-nav a{padding:.48rem;font-size:0}.talent-nav a svg{width:18px}.talent-main{padding:1rem .85rem 5rem}.talent-avatar{width:32px;height:32px} }
    </style>
</head>
<body class="talent-body">
<div class="talent-shell">
    <header class="talent-header">
        <div class="talent-header-inner">
            <a href="{{ community_route('recruiter.landing') }}" class="talent-brand" aria-label="DSCons Talent - {{ brand()->name }}">
                <span class="talent-brand-mark">DS</span><span>DSCons <span class="talent-brand-accent">Talent</span></span>
            </a>
            <span class="talent-community"><x-icon name="layers" size="14" /> {{ brand()->name }}</span>
            @auth
                <nav class="talent-nav" aria-label="Điều hướng Talent">
                    @if(request()->routeIs('community.manage.recruitment.preview.recruiter'))
                        <a href="{{ community_route('manage.recruitment') }}" class="active"><x-icon name="settings" size="16" /> Quản lý Talent</a>
                    @elseif(auth()->user()->isRecruiter())
                        <a href="{{ community_route('recruiter.dashboard') }}" class="{{ request()->routeIs('*recruiter.dashboard','*recruiter.candidates') ? 'active' : '' }}"><x-icon name="search" size="16" /> Ứng viên</a>
                        <a href="{{ community_route('recruiter.plans') }}" class="{{ request()->routeIs('*recruiter.plans') ? 'active' : '' }}"><x-icon name="credit-card" size="16" /> Gói & credit</a>
                        <a href="{{ community_route('recruiter.messages') }}" class="{{ request()->routeIs('*recruiter.messages') ? 'active' : '' }}"><x-icon name="mail" size="16" /> Tin nhắn</a>
                    @else
                        @if(brand()->has_cv)<a href="{{ community_route('engineer.cv') }}" class="{{ request()->routeIs('*engineer.cv','*engineer.recruitment-requests') ? 'active' : '' }}"><x-icon name="cv" size="16" /> CV của tôi</a>@endif
                        <a href="{{ community_route('feed') }}"><x-icon name="users" size="16" /> Cộng đồng</a>
                    @endif
                </nav>
                <img class="talent-avatar" src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}">
            @else
                <a href="{{ route('login') }}" class="talent-btn talent-btn-secondary" style="margin-left:auto;min-height:36px;">Đăng nhập</a>
            @endauth
        </div>
    </header>
    <main class="talent-main">@if(isset($slot)){{ $slot }}@else @yield('content') @endif</main>
</div>
@livewireScripts
</body>
</html>
