<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="color-scheme" content="light">
<meta name="theme-color" content="{{ $brand->theme_primary ?? '#1F77BE' }}">
<title>{{ $title ?? brand()->name }}</title>
<link rel="icon" type="image/png" href="{{ asset('1024x1024-da xoa nen.png') }}">
<meta name="description" content="{{ brand()->name }} — {{ brand()->tagline }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles
<style>
/* ════════════════════════════════════════════════
   DSCons Platform Shell
   ════════════════════════════════════════════════ */

/* Shell viewport — overflow only on body to avoid double-constraint flicker */
/* html bg matches body to prevent white→beige flash on first paint (esp. Retina) */
html { height: 100%; background: {{ $brand->theme_bg ?? '#F7FAFC' }}; }
body { height: 100%; overflow: hidden; font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }

/* Hide Alpine-controlled elements until hydration to prevent FOUC flash */
[x-cloak] { display: none !important; }

/* Impersonation banner adjustments — measured at runtime via min-height,
   but the layout subtractions are deterministic */
body.is-impersonating #app { height: calc(100% - 32px - 36px); }

:root {
    /* Type scale — 4 steps only */
    --fs-xs:   12px;   /* uppercase labels, badge counts */
    --fs-sm:   14px;   /* captions, meta, secondary */
    --fs-base: 16px;   /* body, names, panel text */
    --fs-md:   16px;   /* nav items, titles, headings */

    /* Colors */
    --green:        {{ $brand->theme_primary ?? '#1F77BE' }};
    --green-pale:   {{ $brand->theme_accent ?? '#E1F4F7' }};
    --bg-app:       {{ $brand->theme_bg ?? '#F7FAFC' }};
    --bg-sb:        #FFFFFF;
    --bg-content:   #F7FAFC;
    --bg-card:      #ffffff;
    --bg-hover:     #E7F0F5;
    --bg-active:    #DCECF7;
    --bg-input:     #FFFFFF;
    --text:         #123B59;
    --text-muted:   #61798A;
    --text-ch:      #1A1A1A;
    --text-cat:     #1A1A1A;
    --border:       #D4E1E8;
    --shadow-sm:    0 1px 2px rgba(18,59,89,.06), 0 4px 14px rgba(18,59,89,.04);
    --shadow-md:    0 8px 24px rgba(18,59,89,.08);
    --radius-sm:    8px;
    --radius-md:    12px;
    --motion-fast:  150ms;
    --motion-base: 220ms;

    /* Layout dimensions */
    --guild-w:      72px;
    --sidebar-w:    260px;
    --topbar-h:     48px;
    --user-h:       52px;
    --rp-w:         268px;
    --mob-nav-h:    60px;
}

/* ── Community title bar ── */
#community-bar {
    height: 32px;
    background: var(--bg-app);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: var(--fs-xs);
    font-weight: 600;
    color: var(--text-muted);
    flex-shrink: 0;
}

/* ── App shell: fills viewport exactly ── */
#app {
    display: flex;
    width: 100%;
    height: calc(100% - 32px);
    overflow: hidden;
    background: var(--bg-app);
}

/* CSS containment: isolate paint regions để giảm work khi tab regain focus.
   Mỗi cột có độc lập layer compositor → Mac không rasterize lại toàn page khi switch tab. */
#guild-list, #channel-sidebar, #content-area, #right-panel, #mob-sidebar {
    contain: layout paint style;
}

/* ══ 1. GUILD LIST ══════════════════════════════ */
#guild-list {
    width: var(--guild-w);
    min-width: var(--guild-w);
    background: var(--bg-app);
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 8px 0;
    gap: 8px;
    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-width: none;
    flex-shrink: 0;
}
#guild-list::-webkit-scrollbar { display: none; }

.guild-item {
    position: relative;
    width: var(--guild-w);
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.guild-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 8px;
    background: var(--text);
    border-radius: 0 4px 4px 0;
    transition: height .15s ease;
    pointer-events: none;
}
.guild-item:hover::before        { height: 20px; }
.guild-item.active::before       { height: 40px; background: var(--green); }
.guild-item.active:hover::before { height: 40px; }

.guild-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: #e8e5e0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--fs-sm);
    font-weight: 700;
    color: var(--text-muted);
    cursor: pointer;
    transition: border-radius .15s, background .15s;
    position: relative;
    z-index: 1;
    flex-shrink: 0;
    overflow: hidden;
    text-decoration: none;
}
.guild-item:hover .guild-icon  { border-radius: 16px; }
.guild-item.active .guild-icon { border-radius: 16px; background: var(--green); color: #fff; }
.guild-icon img { width: 100%; height: 100%; object-fit: cover; }

.guild-sep { width: 32px; height: 1px; background: rgba(0,0,0,0.1); flex-shrink: 0; }

/* ══ 2. CHANNEL SIDEBAR ═════════════════════════ */
#channel-sidebar {
    width: var(--sidebar-w);
    min-width: var(--sidebar-w);
    background: var(--bg-sb);
    display: flex;
    flex-direction: column;
    border-right: none;
    border-radius: var(--radius-md) 0 0 0;
    flex-shrink: 0;
    overflow: hidden;
}

/* Community banner at top of sidebar */
#community-banner {
    width: 100%;
    height: 112px;
    background: linear-gradient(135deg, var(--green-pale), var(--bg-hover));
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
    position: relative;
}
#community-banner img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
#community-banner .banner-fallback {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: var(--fs-md);
    font-weight: 700;
    color: var(--green);
}
#community-banner .banner-fallback img {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    object-fit: cover;
}
#community-banner .banner-fallback > span {
    color: #1F77BE;
    font-weight: 700;
}

#sidebar-header {
    height: 52px;
    padding: 0 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
    cursor: pointer;
    transition: background .15s;
    box-shadow: 0 1px 0 var(--border);
}
#sidebar-header:hover {
    background: var(--bg-hover);
}
#sidebar-header h2 {
    font-size: var(--fs-md);
    font-weight: 600;
    color: var(--text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

#channel-list {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 8px;
    scrollbar-width: none;
}
#channel-list::-webkit-scrollbar { display: none; }

.ch-category {
    padding: 16px 8px 4px;
    font-size: var(--fs-xs);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .02em;
    color: var(--text-cat);
    cursor: pointer;
    user-select: none;
    display: flex;
    align-items: center;
    gap: 4px;
}
.ch-category svg { width: 12px; height: 12px; transition: transform .15s; }

.ch-item {
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 40px;
    padding: 8px 10px;
    border-radius: var(--radius-sm);
    color: var(--text-ch);
    font-size: var(--fs-md);
    font-weight: 500;
    line-height: 1.4;
    cursor: pointer;
    margin-bottom: 1px;
    text-decoration: none;
    transition: background .1s, color .1s;
}
.ch-item:hover   { background: var(--bg-hover); color: #1A1A1A; transform: translateX(2px); }
.ch-item.active  { background: var(--bg-active); color: #1A1A1A; font-weight: 600; }
.ch-item svg     { width: 18px; height: 18px; flex-shrink: 0; opacity: .5; }
.ch-item.active svg, .ch-item:hover svg { opacity: 1; }
.ch-item.active svg { color: var(--green); }
.ch-name { flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ch-lock { width: 11px; height: 11px; opacity: .3; flex-shrink: 0; }

.ch-badge {
    font-size: var(--fs-xs);
    font-weight: 700;
    color: #fff;
    background: var(--green);
    border-radius: 9999px;
    padding: 0 5px;
    height: 15px;
    min-width: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* User panel — darker bg like Discord */
#user-panel {
    height: 52px;
    background: var(--bg-app);
    display: flex;
    align-items: center;
    padding: 0 8px;
    gap: 8px;
    flex-shrink: 0;
}
.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--green);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--fs-xs);
    font-weight: 700;
    color: #fff;
    flex-shrink: 0;
    overflow: hidden;
    position: relative;
}
.user-avatar img { width: 100%; height: 100%; object-fit: cover; }
.user-status-dot {
    position: absolute;
    bottom: 0; right: 0;
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #22c55e;
    border: 2px solid var(--bg-app);
}
.user-info  { flex: 1; min-width: 0; }
.user-name  { font-size: var(--fs-sm); font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-stage { font-size: var(--fs-xs); color: var(--green); font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.panel-btn  {
    width: 26px; height: 26px;
    border-radius: 6px; background: transparent; border: none;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    color: var(--text-muted); transition: background .1s;
}
.panel-btn:hover { background: var(--bg-hover); color: var(--text); }
.panel-btn svg { width: 14px; height: 14px; }

/* ══ 3. MAIN AREA ═══════════════════════════════ */
#main-area {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: var(--bg-card);
    overflow: hidden;
    min-width: 0;
}

/* Desktop topbar */
#topbar {
    height: var(--topbar-h);
    background: var(--bg-card);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    padding: 0 14px;
    gap: 10px;
    flex-shrink: 0;
}
.topbar-title {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: var(--fs-md);
    font-weight: 600;
    color: var(--text);
}
.topbar-title svg { color: var(--text-cat); width: 17px; height: 17px; }
.topbar-right { margin-left: auto; display: flex; align-items: center; gap: 6px; }
.topbar-search {
    display: flex; align-items: center; gap: 5px;
    background: var(--bg-input);
    border: 1px solid var(--border);
    border-radius: 6px;
    min-height: 36px;
    padding: 6px 10px;
    font-size: var(--fs-sm);
    color: #1A1A1A;
    min-width: 120px;
    cursor: text;
}
.topbar-search svg { width: 12px; height: 12px; }
.topbar-btn {
    width: 36px; height: 36px;
    border-radius: 6px; background: transparent; border: none;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    color: var(--text-muted); transition: background .1s;
}
.topbar-btn:hover { background: var(--bg-hover); color: var(--text); }
.topbar-btn svg { width: 17px; height: 17px; }

/* Mobile topbar */
#mobile-topbar {
    display: none;
    height: var(--topbar-h);
    background: var(--bg-card);
    border-bottom: 1px solid var(--border);
    align-items: center;
    padding: 0 12px;
    gap: 10px;
    flex-shrink: 0;
}
.mob-menu-btn {
    width: 44px; height: 44px;
    border-radius: 8px; background: var(--bg-hover); border: none;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    color: var(--text-muted);
}
.mob-menu-btn svg { width: 16px; height: 16px; }

/* Content row */
#content-row { flex: 1; display: flex; overflow: hidden; }

#content-area {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 1.75rem;
    scrollbar-width: thin;
    scrollbar-color: rgba(0,0,0,0.08) transparent;
    min-width: 0;
}
#content-area::-webkit-scrollbar { width: 4px; }
#content-area::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.08); border-radius: 2px; }

/* ══ 4. RIGHT PANEL ═════════════════════════════ */
#right-panel {
    width: var(--rp-w);
    min-width: var(--rp-w);
    background: var(--bg-sb);
    border-left: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    flex-shrink: 0;
}
#right-panel-scroll {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    scrollbar-width: thin;
    scrollbar-color: rgba(0,0,0,0.06) transparent;
}
#right-panel-scroll::-webkit-scrollbar { width: 3px; }
#right-panel-scroll::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.08); border-radius: 2px; }

/* Right panel cards */
.rp-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 16px; box-shadow: var(--shadow-sm); }
.rp-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
.rp-card-title { font-size: var(--fs-xs); font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); display: flex; align-items: center; gap: 4px; }
.rp-see-all { font-size: var(--fs-xs); font-weight: 600; color: var(--green); text-decoration: none; }
.rp-see-all:hover { text-decoration: underline; }
.rp-empty { font-size: var(--fs-sm); color: var(--text-cat); text-align: center; padding: 6px 0; }

/* Profile card components */
.profile-user-row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
.profile-avatar { width: 42px; height: 42px; border-radius: 50%; background: var(--green); display: flex; align-items: center; justify-content: center; font-size: var(--fs-sm); font-weight: 700; color: #fff; flex-shrink: 0; overflow: hidden; }
.profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
.profile-name  { font-size: var(--fs-base); font-weight: 700; color: var(--text); }
.profile-title { font-size: var(--fs-sm); color: var(--text-muted); }
.profile-level-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 5px; }
.profile-level { font-size: var(--fs-sm); color: var(--text-muted); }
.profile-exp   { font-size: var(--fs-sm); font-weight: 700; color: var(--green); }
.exp-bar { height: 5px; background: var(--green-pale); border-radius: 3px; margin-bottom: 4px; overflow: hidden; }
.exp-bar-fill { height: 100%; background: var(--green); border-radius: 3px; transition: width .3s ease; }
.exp-next { font-size: var(--fs-xs); color: var(--text-cat); text-align: right; margin-bottom: 10px; }
.profile-stats { display: flex; border-top: 1px solid var(--border); padding-top: 10px; }
.stat-item { flex: 1; text-align: center; }
.stat-item:not(:last-child) { border-right: 1px solid var(--border); }
.stat-value { font-size: var(--fs-md); font-weight: 700; margin-bottom: 2px; }
.stat-label { font-size: var(--fs-xs); color: var(--text-cat); }

/* Leaderboard */
.lb-item { display: flex; align-items: center; gap: 8px; padding: 6px 0; border-bottom: 1px solid var(--border); }
.lb-item:last-child { border-bottom: none; }
.lb-rank  { width: 18px; font-size: var(--fs-sm); font-weight: 700; text-align: center; flex-shrink: 0; }
.lb-av    { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: var(--fs-xs); font-weight: 700; color: #fff; flex-shrink: 0; overflow: hidden; }
.lb-av img{ width: 100%; height: 100%; object-fit: cover; }
.lb-info  { flex: 1; min-width: 0; }
.lb-name  { font-size: var(--fs-sm); font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.lb-exp   { font-size: var(--fs-xs); color: var(--text-cat); }
.lb-badge { font-size: var(--fs-md); }

/* ══ MOBILE ═══════════════════════════════════ */

/* Bottom nav */
#mobile-nav {
    display: none;
    position: fixed;
    bottom: 0; left: 0; right: 0;
    height: var(--mob-nav-h);
    background: var(--bg-card);
    border-top: 1px solid var(--border);
    align-items: center;
    justify-content: space-around;
    z-index: 500;
    padding-bottom: env(safe-area-inset-bottom, 0px);
}
.mob-nav-btn {
    display: flex; flex-direction: column; align-items: center; gap: 3px;
    cursor: pointer; min-width: 64px; min-height: 44px; padding: 6px 12px;
    color: var(--text-muted); text-decoration: none;
    font-size: var(--fs-xs); font-weight: 500;
    border-radius: 8px; transition: color .1s;
}
.mob-nav-btn svg { width: 22px; height: 22px; }
.mob-nav-btn.active { color: var(--green); }

/* Mobile sidebar overlay */
#mob-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 400;
}
#mob-overlay.open { display: block; }

#mob-sidebar {
    position: fixed;
    top: 0; left: 0; bottom: 0;
    width: min(320px, 88vw);
    background: var(--bg-sb);
    z-index: 450;
    transform: translateX(-100%);
    transition: transform .25s ease;
    display: flex; flex-direction: column;
    overflow: hidden;
    padding-top: env(safe-area-inset-top, 0px);
}
#mob-sidebar.open { transform: translateX(0); }
#mob-sidebar #channel-list { display: flex; flex-direction: column; }
.mobile-sidebar-header {
    min-height: 56px;
    padding: 8px 12px 8px 16px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    color: var(--text);
    font-size: var(--fs-md);
    font-weight: 700;
}
.mobile-sidebar-header .panel-btn { width: 40px; height: 40px; }
.mobile-sidebar-header .panel-btn svg { width: 18px; height: 18px; }

/* ══ RESPONSIVE ════════════════════════════════ */
@media (max-width: 1200px) {
    #right-panel { display: none; }
}

@media (max-width: 768px) {
    #community-bar   { display: none; }
    #app             { height: 100%; }
    body.is-impersonating #app { height: calc(100% - 36px); }
    #guild-list      { display: none; }
    #channel-sidebar { display: none; }
    #right-panel     { display: none; }
    #topbar          { display: none; }

    #mobile-topbar { display: flex; }
    #mobile-nav    { display: flex; }

    #main-area { padding-bottom: var(--mob-nav-h); height: 100dvh; }
    #content-area { padding: .75rem; }

    #impersonation-banner { font-size: 11px; }
}

/* Shared interaction and accessibility states. */
:where(a, button, input, select, textarea, [tabindex]):focus-visible {
    outline: 3px solid rgba(31,119,190,.35);
    outline-offset: 2px;
}
button, a, input, select, textarea { touch-action: manipulation; }
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
        scroll-behavior: auto !important;
        transition-duration: .01ms !important;
    }
}
</style>
</head>

<body @if(session('impersonator_id')) class="is-impersonating" @endif>
{{-- Skip link cho keyboard / screen reader --}}
<a href="#content-area" style="position:absolute; left:-9999px; top:0; padding:0.5rem 1rem; background:#fff; color:#123B59; font-size:0.85rem; font-weight:600; z-index:10000; border:2px solid #F39402; border-radius:0.375rem;" onfocus="this.style.left='0.5rem'; this.style.top='0.5rem';" onblur="this.style.left='-9999px';">Bỏ qua điều hướng</a>

{{-- Impersonation banner — visible whenever an admin is "playing" another user --}}
@if(session('impersonator_id'))
    @php $__impersonator = \App\Models\User::find(session('impersonator_id')); @endphp
    <div id="impersonation-banner" role="alert" style="
        min-height: 36px;
        background: #7c2d12;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-size: 13px;
        font-weight: 600;
        flex-shrink: 0;
        padding: 6px 12px;
        flex-wrap: wrap;
    ">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true" style="flex-shrink:0;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-2.75L13.74 4a2 2 0 00-3.48 0L3.16 16.25A2 2 0 005 19z"/>
        </svg>
        <span>
            Đang đóng vai
            <strong style="color:#fef3c7;">{{ auth()->user()->name }}</strong>
            (@<span>{{ auth()->user()->username ?? auth()->id() }}</span>)
            @if($__impersonator)
                — admin <strong>{{ $__impersonator->name }}</strong>
            @endif
        </span>
        <form method="POST" action="{{ route('admin.impersonate.stop') }}" style="display:inline; flex-shrink:0;">
            @csrf
            <button type="submit" style="
                background: #fef3c7;
                color: #7c2d12;
                border: none;
                padding: 8px 14px;
                min-height: 36px;
                border-radius: 6px;
                font-size: 12px;
                font-weight: 700;
                cursor: pointer;
            ">Dừng đóng vai</button>
        </form>
    </div>
@endif


{{-- Community title bar --}}
<div id="community-bar">
    @if(isset($brand) && $brand->logo_path)
    <img src="{{ asset('storage/' . $brand->logo_path) }}" alt="" style="width:20px; height:20px; border-radius:5px; object-fit:cover;">
    @else
    <img src="{{ asset('1024x1024-da xoa nen.png') }}" alt="DSCons" style="width:20px;height:20px;object-fit:contain;">
    @endif
    <span>{{ $brand->name ?? 'DSCons' }}</span>
</div>

<div id="app">

{{-- 1. GUILD LIST ──────────────────────────── --}}
<div id="guild-list">
    {{-- Home / Messages icon (like Discord DM button) --}}
    @auth
    <div class="guild-item">
        <a href="{{ route('messages') }}" class="guild-icon" title="Tin nhắn" style="background:var(--bg-card);">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--text-muted);"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        </a>
    </div>
    <div class="guild-sep"></div>
    @endauth
    @auth
    @foreach(auth()->user()->memberships()->with('brand')->whereIn('status', ['active','trial'])->get()->unique('brand_id') as $m)
        <div class="guild-item {{ $m->brand_id === $brand->id ? 'active' : '' }}">
            <a href="https://{{ $m->brand->domain }}/feed" class="guild-icon" title="{{ $m->brand->name }}">
                @if($m->brand->logo_path)
                    <img src="{{ asset('storage/' . $m->brand->logo_path) }}" alt="{{ $m->brand->slug }}">
                @else
                    <img src="{{ asset('1024x1024-da xoa nen.png') }}" alt="DSCons" style="width:34px;height:34px;object-fit:contain;">
                @endif
            </a>
        </div>
    @endforeach
    @endauth
    <div class="guild-sep"></div>
</div>

{{-- 2. CHANNEL SIDEBAR ──────────────────────── --}}
<div id="channel-sidebar">
    {{-- Community banner --}}
    <div id="community-banner">
        @if(isset($brand) && $brand->banner_path)
            <img src="{{ asset('storage/' . $brand->banner_path) }}" alt="">
        @else
            <div class="banner-fallback">
                @if(isset($brand) && $brand->logo_path)
                <img src="{{ asset('storage/' . $brand->logo_path) }}" alt="">
                @else
                <img src="{{ asset('1024x1024-da xoa nen.png') }}" alt="DSCons" style="width:58px;height:58px;object-fit:contain;">
                @endif
                <span>DSCons Việt Nam</span>
            </div>
        @endif
    </div>

    <div id="sidebar-header">
        <h2>{{ $brand->name }}</h2>
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0; color:var(--text-muted);"><polyline points="6 9 12 15 18 9" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </div>

    <div id="channel-list">
        <div class="ch-category">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
            Cộng đồng DSCons
        </div>
        <a href="{{ route('feed') }}" class="ch-item {{ request()->routeIs('feed') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            <span class="ch-name">Bảng tin</span>
        </a>
        <a href="{{ route('cot') }}" class="ch-item {{ request()->routeIs('cot') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            <span class="ch-name">Kiến thức cốt lõi</span>
        </a>
        <a href="{{ route('signals') }}" class="ch-item {{ request()->routeIs('signals') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
            <span class="ch-name">Tín hiệu công nghệ</span>
        </a>
        <a href="{{ route('qa') }}" class="ch-item {{ request()->routeIs('qa') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="ch-name">Hỏi đáp kỹ thuật</span>
        </a>

        <div class="ch-category">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
            Học tập
        </div>
        <a href="{{ route('academy') }}" class="ch-item {{ request()->routeIs('academy*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/></svg>
            <span class="ch-name">Khóa học</span>
        </a>
        <a href="{{ route('challenge') }}" class="ch-item {{ request()->routeIs('challenge*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
            <span class="ch-name">Challenge</span>
        </a>
        <a href="{{ route('leaderboard') }}" class="ch-item {{ request()->routeIs('leaderboard') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/></svg>
            <span class="ch-name">Bảng xếp hạng</span>
        </a>

        <div class="ch-category">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
            Khác
        </div>
        <a href="{{ route('marketplace') }}" class="ch-item {{ request()->routeIs('marketplace') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
            <span class="ch-name">Marketplace</span>
        </a>
        <a href="{{ route('affiliate') }}" class="ch-item {{ request()->routeIs('affiliate') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span class="ch-name">Affiliate</span>
        </a>

        @can('admin')
        <div class="ch-category">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
            Admin
        </div>
        <a href="{{ route('admin.dashboard') }}" class="ch-item {{ request()->routeIs('admin.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
            <span class="ch-name">Admin</span>
        </a>
        @endcan
    </div>

    @auth
    <div id="user-panel">
        <a href="{{ route('profile', auth()->user()->username ?? auth()->id()) }}" class="user-avatar" style="text-decoration:none;">
            <img src="{{ auth()->user()->avatar_url }}" alt="">
            <div class="user-status-dot"></div>
        </a>
        <div class="user-info">
            <div class="user-name">{{ auth()->user()->name }}</div>
            <div class="user-stage">{{ auth()->user()->class_label }} · {{ auth()->user()->job_stage }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="display:flex;">
            @csrf
            <button type="submit" class="panel-btn" title="Đăng xuất">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </button>
        </form>
    </div>
    @endauth
</div>

{{-- 3. MAIN AREA ──────────────────────────────── --}}
<div id="main-area">

    {{-- Mobile topbar --}}
    <div id="mobile-topbar">
        <button class="mob-menu-btn" onclick="openSidebar()" aria-label="Menu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <span style="flex:1;font-size:var(--fs-md);font-weight:600;color:var(--text)">{{ $title ?? 'Feed' }}</span>
        @auth
        <livewire:notification-bell />
        @endauth
    </div>

    {{-- Desktop topbar --}}
    <div id="topbar">
        <div class="topbar-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="width:17px;height:17px"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            {{ $title ?? 'Feed' }}
        </div>
        <div class="topbar-right">
            @auth
            <livewire:notification-bell />
            <a href="{{ route('messages') }}" class="topbar-btn" title="Tin nhắn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </a>
            @endauth
            <div class="topbar-search" x-data role="button" tabindex="0" aria-label="Mở tìm kiếm"
                 @click="window.location.href='{{ route('search') }}'"
                 @keydown.enter="window.location.href='{{ route('search') }}'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                Tìm kiếm...
            </div>
        </div>
    </div>

    {{-- Site-wide holiday/freeze banner --}}
    @php
        $__freezeBrandId = $brand->id ?? 0;
        $__freezeExp = cache()->remember('layout_freeze_banner_b'.$__freezeBrandId.'_v2', 30, function() use ($__freezeBrandId) {
            $q = \App\Models\Expedition::where('status', 'active')
                ->whereNotNull('freeze_from_day')
                ->whereNotNull('freeze_ends_at')
                ->where('freeze_ends_at', '>', now());
            if ($__freezeBrandId) {
                $q->where('brand_id', $__freezeBrandId);
            }
            return $q->first();
        });
    @endphp
    @if($__freezeExp)
    <div role="region" aria-label="Thông báo nghỉ lễ" style="background:#FEF3C7; border-bottom:1px solid #FDE68A; padding:0.625rem 1rem; flex-shrink:0; display:flex; align-items:baseline; gap:0.5rem; flex-wrap:wrap;">
        <span style="font-size:0.85rem; font-weight:700; color:#92400E; flex-shrink:0;"><span aria-hidden="true">🎉</span> Nghỉ lễ 30/4 - 1/5</span>
        <span style="font-size:0.78rem; color:#78716C;">Toàn bộ thử thách tạm dừng từ 23:57 tối 29/04 đến 07:00 sáng Thứ 2 04/05. Mọi hạn nộp được đóng băng, không ai bị tính trễ. Cứ thoải mái nghỉ ngơi, Thứ 2 quay lại làm tiếp.</span>
    </div>
    @endif

    <div id="content-row">

        {{-- Page content --}}
        <div id="content-area">
            {{ $slot }}
        </div>

        {{-- 4. RIGHT PANEL — DSCons gamification ──── --}}
        <div id="right-panel">
            <div id="right-panel-scroll">
                @auth
                {{-- My Profile Card --}}
                <div class="rp-card">
                    <div class="profile-user-row">
                        <div class="profile-avatar">
                            <img src="{{ auth()->user()->avatar_url }}" alt="">
                        </div>
                        <div>
                            <div class="profile-name">{{ auth()->user()->name }}</div>
                            <div class="profile-title">{{ auth()->user()->class_emoji }} {{ auth()->user()->class_label }}</div>
                        </div>
                    </div>
                    <div class="profile-level-row">
                        <span class="profile-level">Lv.{{ auth()->user()->level }} · {{ auth()->user()->job_stage }}</span>
                        <span class="profile-exp">{{ number_format(auth()->user()->xp) }} XP</span>
                    </div>
                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-value" style="color:var(--green)">{{ auth()->user()->aip ?? 0 }}</div>
                            <div class="stat-label">AIP</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value" style="color:#6366f1">{{ auth()->user()->da_count }}</div>
                            <div class="stat-label">💎 Đá</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value" style="color:#ef4444">{{ auth()->user()->streak ?? 0 }}</div>
                            <div class="stat-label">Streak</div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar widgets --}}
                <livewire:sidebar-challenge />
                <livewire:sidebar-leaderboard />
                @endauth
            </div>
        </div>

    </div>
</div>

{{-- MOBILE: Overlay + sidebar ───────────────── --}}
<div id="mob-overlay" onclick="closeSidebar()"></div>
<div id="mob-sidebar">
    <div class="mobile-sidebar-header">
        <span>{{ $brand->name }}</span>
        <button type="button" class="panel-btn" onclick="closeSidebar()" aria-label="Đóng menu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
    </div>
    <div id="channel-list" style="flex:1;overflow-y:auto;padding:6px;">
        <div class="ch-category">Cộng đồng DSCons</div>
        <a href="{{ route('feed') }}" class="ch-item {{ request()->routeIs('feed')?'active':'' }}" onclick="closeSidebar()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            <span class="ch-name">Bảng tin</span>
        </a>
        <a href="{{ route('cot') }}" class="ch-item {{ request()->routeIs('cot')?'active':'' }}" onclick="closeSidebar()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            <span class="ch-name">Kiến thức cốt lõi</span>
        </a>
        <a href="{{ route('signals') }}" class="ch-item {{ request()->routeIs('signals')?'active':'' }}" onclick="closeSidebar()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
            <span class="ch-name">Tín hiệu công nghệ</span>
        </a>
        <a href="{{ route('qa') }}" class="ch-item {{ request()->routeIs('qa')?'active':'' }}" onclick="closeSidebar()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="ch-name">Hỏi đáp kỹ thuật</span>
        </a>
        <div class="ch-category">Học tập</div>
        <a href="{{ route('academy') }}" class="ch-item {{ request()->routeIs('academy*')?'active':'' }}" onclick="closeSidebar()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/></svg>
            <span class="ch-name">Khóa học</span>
        </a>
        <a href="{{ route('challenge') }}" class="ch-item {{ request()->routeIs('challenge*')?'active':'' }}" onclick="closeSidebar()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
            <span class="ch-name">Challenge</span>
        </a>
        <a href="{{ route('leaderboard') }}" class="ch-item {{ request()->routeIs('leaderboard')?'active':'' }}" onclick="closeSidebar()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/></svg>
            <span class="ch-name">Bảng xếp hạng</span>
        </a>
    </div>
    @auth
    <div id="user-panel">
        <div class="user-avatar">
            <img src="{{ auth()->user()->avatar_url }}" alt="">
        </div>
        <div class="user-info">
            <div class="user-name">{{ auth()->user()->name }}</div>
            <div class="user-stage">{{ auth()->user()->class_label }} · {{ auth()->user()->job_stage }}</div>
        </div>
    </div>
    @endauth
</div>

{{-- MOBILE: Bottom nav ───────────────────────── --}}
@auth
<nav id="mobile-nav">
    <a href="{{ route('feed') }}" class="mob-nav-btn {{ request()->routeIs('feed')?'active':'' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
        Home
    </a>
    <a href="{{ route('qa') }}" class="mob-nav-btn {{ request()->routeIs('qa')?'active':'' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Hỏi đáp
    </a>
    <a href="{{ route('challenge') }}" class="mob-nav-btn {{ request()->routeIs('challenge*')?'active':'' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
        Challenge
    </a>
    <a href="{{ route('profile', auth()->user()->username ?? auth()->id()) }}" class="mob-nav-btn {{ request()->routeIs('profile')?'active':'' }}">
        <img src="{{ auth()->user()->avatar_url }}" style="width:22px;height:22px;border-radius:50%;object-fit:cover;" alt="">
        Tôi
    </a>
</nav>
@endauth

</div>{{-- /#app --}}

{{-- Feedback FAB (authenticated users only) --}}
@auth
<livewire:feedback-button />
@endauth

{{-- Post Modal (global) --}}
<livewire:post-modal />

{{-- Toast Notifications --}}
<div x-data="{ toasts: [], add(e) { const t = { id: Date.now(), message: e.detail.message, type: e.detail.type || 'info' }; this.toasts.push(t); setTimeout(() => this.toasts = this.toasts.filter(x => x.id !== t.id), 4000); } }"
     @toast.window="add($event)"
     style="position:fixed; bottom:5rem; right:1rem; z-index:9999; display:flex; flex-direction:column; gap:0.5rem;">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             :style="'padding:0.625rem 1rem; border-radius:0.5rem; font-size:0.8rem; font-weight:500; box-shadow:0 4px 12px rgba(0,0,0,0.15); max-width:320px;'
                + (toast.type === 'error' ? 'background:#FEE2E2; color:#991B1B; border:1px solid #FECACA;' :
                   toast.type === 'success' ? 'background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0;' :
                   'background:#FFFFFF; color:#1A1A1A; border:1px solid #E1E1E1;')"
             x-text="toast.message">
        </div>
    </template>
</div>

@if(session('toast'))
<script>
window.addEventListener('DOMContentLoaded', () => {
    window.dispatchEvent(new CustomEvent('toast', { detail: @json(session('toast')) }));
});
</script>
@endif

@livewireScripts

<script>
function openSidebar()  {
    document.getElementById('mob-sidebar').classList.add('open');
    document.getElementById('mob-overlay').classList.add('open');
}
function closeSidebar() {
    document.getElementById('mob-sidebar').classList.remove('open');
    document.getElementById('mob-overlay').classList.remove('open');
}
</script>

@stack('scripts')
</body>
</html>
