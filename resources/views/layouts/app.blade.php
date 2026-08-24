<!DOCTYPE html>
<html lang="vi">
<head>
@php
    // A brand may have empty theme fields in an older local database; keep the DSCons palette usable.
    $__brandPrimary = filled($brand->theme_primary ?? null) ? $brand->theme_primary : '#1F77BE';
    $__brandAccent = filled($brand->theme_accent ?? null) ? $brand->theme_accent : '#E1F4F7';
    $__brandBg = filled($brand->theme_bg ?? null) ? $brand->theme_bg : '#F7FAFC';
@endphp
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="color-scheme" content="light">
<meta name="theme-color" content="{{ $__brandPrimary }}">
<title>{{ $title ?? brand()->name }}</title>
<link rel="icon" type="image/png" href="{{ asset('1024x1024-da xoa nen.png') }}">
<meta name="description" content="{{ brand()->name }} — {{ brand()->tagline }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@stack('head')
@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles
<style>
/* ════════════════════════════════════════════════
   DSCons Platform Shell
   ════════════════════════════════════════════════ */

/* Shell viewport — overflow only on body to avoid double-constraint flicker */
/* html bg matches body to prevent white→beige flash on first paint (esp. Retina) */
html { height: 100%; background: {{ $__brandBg }}; }
body { height: 100%; overflow: hidden; font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }

/* Hide Alpine-controlled elements until hydration to prevent FOUC flash */
[x-cloak] { display: none !important; }

/* Impersonation banner adjustments — measured at runtime via min-height,
   but the layout subtractions are deterministic */
body.is-impersonating #app { height: calc(100% - 36px); }

:root {
    /* Type scale — 4 steps only */
    --fs-xs:   11px;   /* uppercase labels, badge counts */
    --fs-sm:   13px;   /* captions, meta, secondary */
    --fs-base: 15px;   /* body, names, panel text */
    --fs-md:   15px;   /* nav items, titles, headings */

    /* Colors */
    --green:        {{ $__brandPrimary }};
    --green-pale:   {{ $__brandAccent }};
    --bg-app:       {{ $__brandBg }};
    --bg-sb:        #FFFFFF;
    --bg-content:   #F7FAFC;
    --bg-card:      #ffffff;
    --bg-hover:     #E7F0F5;
    --bg-active:    #E1F4F7;
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
    --guild-w:      64px;
    --sidebar-w:    228px;
    --topbar-h:     54px;
    --user-h:       52px;
    --rp-w:         264px;
    --mob-nav-h:    64px;
}

/* ── Community title bar ── */
#community-bar { display: none; }

/* ── App shell: fills viewport exactly ── */
#app {
    display: flex;
    width: 100%;
    height: 100%;
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
    display: none;
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
    width: 42px;
    height: 42px;
    border-radius: 13px;
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
.guild-item:hover .guild-icon  { border-radius: 16px; background: var(--bg-hover); }
.guild-item.active .guild-icon { border-radius: 16px; background: var(--green); color: #fff; box-shadow: 0 5px 14px rgba(31,119,190,.18); }
.guild-icon img { width: 100%; height: 100%; object-fit: cover; }

.guild-sep { width: 32px; height: 1px; background: rgba(0,0,0,0.1); flex-shrink: 0; }

/* ══ 2. CHANNEL SIDEBAR ═════════════════════════ */
#channel-sidebar {
    width: var(--sidebar-w);
    min-width: var(--sidebar-w);
    background: #F9FCFD;
    display: flex;
    flex-direction: column;
    border-right: 1px solid var(--border);
    border-radius: 0;
    flex-shrink: 0;
    overflow: hidden;
}

/* Community banner at top of sidebar */
#community-banner {
    width: 100%;
    height: 78px;
    background: #E1F4F7;
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
    gap: 9px;
    font-size: var(--fs-md);
    font-weight: 700;
    color: var(--green);
}
#community-banner .banner-fallback img {
    width: 42px;
    height: 42px;
    border-radius: 11px;
    object-fit: cover;
}
#community-banner .banner-fallback > span {
    display: none;
}

#sidebar-header {
    min-height: 68px;
    margin: 8px 8px 6px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
    border: 0;
    background: transparent;
    box-shadow: none;
}
.community-switcher-main { display: flex; align-items: center; gap: 2px; width: 100%; min-width: 0; min-height: 66px; padding: 5px; border: 1px solid var(--border); border-radius: 15px; background: #fff; box-shadow: 0 4px 14px rgba(18,59,89,.06); transition: background .15s ease, border-color .15s ease, box-shadow .15s ease; }
#sidebar-header.is-open .community-switcher-main { border-color: #B9D7E2; background: #EEF7F9; box-shadow: 0 6px 16px rgba(18,59,89,.08); }
.community-switcher-chevron, .community-switcher-bell {
    width: 38px; height: 40px; flex: 0 0 auto; border: 0; border-radius: 10px;
    display: grid; place-items: center; background: transparent; color: var(--text-muted);
    cursor: pointer;
}
.community-switcher-chevron:hover, .community-switcher-bell:hover { background: var(--bg-hover); color: var(--text); }
.community-switcher-chevron:focus-visible, .community-switcher-button:focus-visible, .community-switcher-bell:focus-visible { outline: 3px solid rgba(31,119,190,.22); outline-offset: 1px; }
.community-switcher-chevron svg { width: 15px; height: 15px; }
.community-switcher-bell { margin-left: auto; }
.community-switcher-bell svg { width: 18px; height: 18px; }
.community-switcher-main > .notification-bell { margin-left: auto; flex: 0 0 auto; }
.community-switcher-main > .notification-bell > button { width: 38px; height: 38px; color: var(--text-muted) !important; }
#sidebar-header h2 {
    font-size: var(--fs-md);
    font-weight: 600;
    color: var(--text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.community-switcher-button {
    width: auto; flex: 1 1 auto; min-width: 0; min-height: 52px; padding: 5px 7px; border: 0; border-radius: 11px;
    background: transparent; color: var(--text); display: flex; align-items: center; gap: 9px;
    text-align: left; cursor: pointer; font: inherit;
}
.community-switcher-button:hover, .community-switcher-button[aria-expanded="true"] { background: transparent; }
.community-switcher-menu {
    position: absolute; z-index: 100; top: calc(100% + 7px); left: 0; right: 0;
    max-height: min(430px, calc(100vh - 170px)); overflow-y: auto; padding: 8px;
    border: 1px solid var(--border); border-radius: 14px; background: #fff;
    box-shadow: 0 16px 34px rgba(18,59,89,.16);
}
.community-switcher-item {
    min-height: 46px; padding: 8px; border-radius: 9px; display: flex; align-items: center;
    gap: 9px; color: var(--text); text-decoration: none; font-size: 13px; font-weight: 650;
}
.community-switcher-item:hover, .community-switcher-item.active { background: var(--bg-active); }
.community-switcher-logo { width: 38px; height: 38px; border-radius: 11px; object-fit: cover; flex: 0 0 auto; background: #E1F4F7; }
.community-switcher-discovery-icon { display: grid; place-items: center; color: var(--green); }
.community-switcher-discovery-icon svg { width: 17px; height: 17px; }
.community-switcher-discovery-label { font-size: 13px !important; letter-spacing: -.025em; }
.community-stat-grid span { display: block; color: var(--text-muted); font-size: 10px; line-height: 1.2; white-space: nowrap; }
.community-switcher-footer { display: grid; grid-template-columns: 1fr; gap: 6px; padding-top: 8px; margin-top: 4px; border-top: 1px solid var(--border); }
.community-switcher-footer a { min-height: 40px; display: flex; align-items: center; padding: 0 11px; border-radius: 9px; background: #F4F8FA; color: var(--text); text-decoration: none; font-size: 12px; font-weight: 700; }
.community-switcher-footer a:hover { background: var(--bg-active); color: var(--green); }

#channel-list {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 10px 9px 12px;
    scrollbar-width: none;
}
#channel-list::-webkit-scrollbar { display: none; }

.ch-category {
    padding: 15px 8px 5px;
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
.ch-category svg { width: 12px; height: 12px; color: var(--text-muted); stroke: currentColor; transition: transform .15s; }

.ch-item {
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 42px;
    padding: 8px 10px;
    border-radius: 10px;
    color: var(--text-ch);
    font-size: var(--fs-md);
    font-weight: 500;
    line-height: 1.4;
    cursor: pointer;
    margin-bottom: 1px;
    text-decoration: none;
    transition: background-color .16s ease, color .16s ease, box-shadow .16s ease;
}
.ch-item:hover   { background: var(--bg-hover); color: var(--text); }
.ch-item.active  { background: var(--bg-active); color: var(--green); font-weight: 650; box-shadow: inset 3px 0 0 var(--green); }
.ch-item svg     { width: 18px; height: 18px; flex-shrink: 0; color: var(--green, #1F77BE); opacity: .82; stroke-width: 1.8; }
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
    background: #F2F8FA;
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
    width: 40px; height: 40px;
    border-radius: 10px; background: transparent; border: none;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    color: var(--text-muted); transition: background-color .16s ease, color .16s ease;
}
.panel-btn:hover { background: var(--bg-hover); color: var(--text); }
.panel-btn svg { width: 14px; height: 14px; }

/* ══ 3. MAIN AREA ═══════════════════════════════ */
#main-area {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: var(--bg-content);
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
    padding: 0 18px;
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
.topbar-title svg { color: var(--green); width: 17px; height: 17px; stroke: currentColor; }
.topbar-right { margin-left: auto; display: flex; align-items: center; gap: 6px; }
.topbar-search {
    display: flex; align-items: center; gap: 5px;
    background: var(--bg-input);
    border: 1px solid var(--border);
    border-radius: 10px;
    min-height: 40px;
    padding: 6px 10px;
    font-size: var(--fs-sm);
    color: #1A1A1A;
    min-width: 120px;
    cursor: text;
}
.topbar-search svg { width: 12px; height: 12px; }
.topbar-btn {
    width: 40px; height: 40px;
    border-radius: 10px; background: transparent; border: none;
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
    padding: 1.5rem clamp(1rem, 2.5vw, 2rem);
    scrollbar-width: thin;
    scrollbar-color: rgba(0,0,0,0.08) transparent;
    min-width: 0;
}
#content-area::-webkit-scrollbar { width: 4px; }
#content-area::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.08); border-radius: 2px; }

/* Admin uses the same visual language as the learning product, with denser data
   surfaces and stronger separation between tools and content. */
body.is-admin-area #content-area { background: #F7FAFC; }
body.is-admin-area .card { border-radius: 16px; border-color: #D7E5EA; }
body.is-admin-area .page-heading { padding-bottom: 14px; border-bottom: 1px solid #DDE9EE; }
body.is-admin-area .input:focus { border-color: #1F77BE; box-shadow: 0 0 0 3px rgba(31,119,190,.14); }
body.is-admin-area table { border-collapse: separate; border-spacing: 0; }
body.is-admin-area th { color: var(--text-muted); font-size: 11px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
body.is-admin-area td { color: var(--text-secondary); }
body.is-admin-area .admin-table-scroll { border: 1px solid #D7E5EA; border-radius: 16px; box-shadow: 0 2px 8px rgba(18,59,89,.035); }
body.is-admin-area .admin-table-scroll thead th { position: sticky; top: 0; z-index: 2; background: #F7FAFC; border-bottom: 1px solid #D7E5EA; }
body.is-admin-area .admin-table-scroll tbody tr:last-child td { border-bottom: 0 !important; }
body.is-admin-area .admin-table-scroll tbody tr:hover td { background: #FBFDFE; }
body.is-admin-area .admin-modal-card { border-radius: 18px; box-shadow: 0 24px 72px rgba(18,59,89,.22); }
body.is-admin-area .admin-modal-backdrop :where(input, select, textarea):focus { position: relative; z-index: 1; }
@media (max-width: 640px) {
    body.is-admin-area #content-area { padding: 1rem .75rem calc(var(--mob-nav-h) + 1rem); }
    body.is-admin-area .admin-modal-backdrop { align-items: flex-end !important; padding: .75rem !important; }
    body.is-admin-area .admin-modal-card { max-height: calc(100dvh - 1.5rem) !important; border-radius: 18px !important; }
}

/* ══ 4. RIGHT PANEL ═════════════════════════════ */
#right-panel {
    width: var(--rp-w);
    min-width: var(--rp-w);
    background: #F4FAFC;
    border-left: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    flex-shrink: 0;
}
#right-panel-scroll {
    flex: 1;
    overflow-y: auto;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    scrollbar-width: thin;
    scrollbar-color: rgba(0,0,0,0.06) transparent;
}
#right-panel-scroll::-webkit-scrollbar { width: 3px; }
#right-panel-scroll::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.08); border-radius: 2px; }

/* Right panel cards */
.rp-card { background: var(--bg-card); border: 1px solid #D9E6EC; border-radius: 16px; padding: 15px; box-shadow: 0 2px 8px rgba(18,59,89,.035); }
.rp-hero-fallback { position:absolute; inset:0; display:grid; place-items:center; color:rgba(255,255,255,.9); background:#125A96; }
.rp-hero-fallback-inner { display:grid; justify-items:center; gap:6px; text-align:center; padding:16px; }
.rp-hero-fallback-inner svg { width:30px; height:30px; }
.rp-hero-fallback-inner strong { font-size:12px; letter-spacing:.02em; }
.rp-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
.rp-card-title { font-size: var(--fs-xs); font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); display: flex; align-items: center; gap: 4px; }
.rp-see-all { font-size: var(--fs-xs); font-weight: 600; color: var(--green); text-decoration: none; }
.rp-see-all:hover { text-decoration: underline; }
.rp-empty { font-size: var(--fs-sm); color: var(--text-cat); text-align: center; padding: 6px 0; }
.membership-cta { display: flex !important; align-items: center; justify-content: center; gap: 6px; border: 1px solid #1F77BE !important; border-radius: 12px !important; background: #1F77BE !important; color: #fff !important; font-size: .78rem !important; line-height: 1.2; white-space: nowrap; font-weight: 750 !important; box-shadow: 0 5px 12px rgba(31,119,190,.2); transition: transform .15s ease, background .15s ease, box-shadow .15s ease; }
.membership-cta.is-active { border-color: #0E527F !important; background: #0E527F !important; }
.membership-cta:hover { background: #125A96 !important; color: #fff !important; box-shadow: 0 8px 16px rgba(18,90,150,.26); transform: translateY(-1px); }
.membership-cta:active { transform: translateY(0); box-shadow: 0 2px 6px rgba(18,90,150,.2); }
.membership-cta svg { width: 15px; height: 15px; }
.community-invite-button { border-color: #B8D8E4 !important; border-radius: 12px !important; background: #EAF7FA !important; color: #125A96 !important; font-weight: 750 !important; transition: background .15s ease, border-color .15s ease, transform .15s ease; }
.community-invite-button:hover { border-color: #1F77BE !important; background: #D9F0F5 !important; transform: translateY(-1px); }

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
.mob-nav-btn.active { color: var(--green); background: var(--bg-active); }

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

    #main-area { padding-bottom: calc(var(--mob-nav-h) + env(safe-area-inset-bottom, 0px)); height: 100dvh; scroll-padding-bottom: calc(var(--mob-nav-h) + env(safe-area-inset-bottom, 0px)); }
    #content-area { padding: .75rem; }
    .mob-nav-btn { min-width: 56px; padding-left: 8px; padding-right: 8px; }

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

/* DSCons feed direction: clear, spacious BIM/MEP community workspace. */
body.dscons-shell {
    --green: #1F77BE;
    --green-pale: #DCECF7;
    --bg-app: #EEF7FB;
    --bg-content: #EEF7FB;
    --bg-hover: #E7F2F8;
    --bg-active: #DCECF7;
    --border: #C9DFEA;
    --dscons-sand: #EEF7FB;
    --dscons-surface: #FFFFFF;
    --dscons-ink: #102A3B;
    --dscons-muted: #61798A;
    --dscons-brand: #1F77BE;
    --dscons-brand-soft: #DCECF7;
    --dscons-border: #C9DFEA;
    --sidebar-w: 304px;
    --rp-w: 314px;
    background: var(--dscons-sand);
}
body.dscons-shell #app,
body.dscons-shell #main-area,
body.dscons-shell #content-row { background: var(--dscons-sand); }
body.dscons-shell #channel-sidebar {
    background: #F7FBFD;
    border-right-color: var(--dscons-border);
}
body.dscons-shell #content-area {
    padding: 1.25rem clamp(1rem, 2vw, 2rem) 3rem;
    background: var(--dscons-sand);
}
body.dscons-shell #right-panel {
    background: #F7FBFD;
    border-left-color: var(--dscons-border);
}
body.dscons-shell #right-panel-scroll { padding: 12px 14px; gap: 10px; }
body.dscons-shell .rp-card,
body.dscons-shell .card {
    background: var(--dscons-surface);
    border-color: var(--dscons-border);
    box-shadow: 0 2px 8px rgba(18, 59, 89, .04);
}
body.dscons-shell .ch-item { color: var(--dscons-ink); }
body.dscons-shell .ch-item:hover { background: #E7F2F8; color: #102A3B; }
body.dscons-shell .ch-item.active { background: #DCECF7; color: #102A3B; }
body.dscons-shell .ch-item.active { box-shadow: inset 3px 0 0 var(--dscons-brand); }
body.dscons-shell .topbar-title { color: var(--dscons-ink); }
body.dscons-shell .topbar-title svg,
body.dscons-shell .ch-item svg { color: var(--dscons-brand); }
body.dscons-shell .membership-cta {
    background: #1F77BE !important;
    border-color: #1F77BE !important;
}
body.dscons-shell .membership-cta:hover { background: #125A96 !important; }
@media (max-width: 1200px) {
    body.dscons-shell { --sidebar-w: 260px; }
}
@media (max-width: 768px) {
    body.dscons-shell #content-area { padding: .75rem .65rem calc(var(--mob-nav-h) + 1rem); }
}
</style>
</head>

<body class="@if(session('impersonator_id')) is-impersonating @endif @if(request()->routeIs('admin.*')) is-admin-area @endif @if(($brand->slug ?? null) === 'dscons') dscons-shell @endif">
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
        <a href="{{ route('messages') }}" class="guild-icon" title="Tin nhắn" aria-label="Mở tin nhắn" style="background:var(--bg-card);">
            <x-icon name="chat" size="20" color="var(--ds-text-muted)" />
        </a>
    </div>
    <div class="guild-sep"></div>
    @endauth
    @auth
    @foreach(auth()->user()->memberships()->withoutGlobalScopes()->with('brand')->whereIn('status', ['active','trial'])->get()->unique('brand_id') as $m)
        <div class="guild-item {{ $m->brand_id === $brand->id ? 'active' : '' }}">
            <a href="{{ route('community.feed', ['community' => $m->brand->slug]) }}" class="guild-icon" title="{{ $m->brand->name }}">
                @if(($m->brand->slug ?? null) !== 'dscons' && $m->brand->logo_path)
                    <img src="{{ asset('storage/' . $m->brand->logo_path) }}" alt="{{ $m->brand->slug }}">
                @else
                    <img src="{{ asset('1024x1024-da xoa nen.png') }}" alt="DSCons" style="width:34px;height:34px;object-fit:contain;">
                @endif
            </a>
        </div>
    @endforeach
    @endauth
    <div class="guild-sep"></div>
    <div class="guild-item">
        <a href="{{ route('communities') }}" class="guild-icon" title="Khám phá cộng đồng" aria-label="Khám phá cộng đồng" style="background:#E1F4F7;color:#1F77BE;font-size:22px;">+</a>
    </div>
</div>

{{-- 2. CHANNEL SIDEBAR ──────────────────────── --}}
<div id="channel-sidebar">
    @php
        $__shell = \App\Support\CommunityShellData::make($brand, auth()->user(), request()->routeIs('communities'));
        $__sidebarMemberships = $__shell['sidebarMemberships'];
        $__isDiscoveryContext = $__shell['isDiscoveryContext'];
        $__discoveryMemberships = $__shell['discoveryMemberships'];
        $__currentMembership = $__shell['currentMembership'];
        $__featuredUpgrade = $__shell['featuredUpgrade'];
        $__communityMembers = $__shell['communityMembers'];
        $__communityMemberCount = $__shell['communityMemberCount'];
        $__communityActiveCount = $__shell['communityActiveCount'];
        $__communityAdminCount = $__shell['communityAdminCount'];
        $__isPremiumMember = $__shell['isPremiumMember'];
        $__isCommunityParticipant = $__shell['isCommunityParticipant'];
        $__membershipLabel = $__shell['membershipLabel'];
        $__memberAvatarSize = $__shell['memberAvatarSize'];
        $__freezeExp = $__shell['freezeExpedition'];
        $__membershipDisplayLabel = mb_strtoupper($__membershipLabel, 'UTF-8');
        $__currentUser = auth()->user();
        $__activityLabel = ! $__currentUser ? 'Khám phá cộng đồng' : match (true) {
            $__currentUser->streak >= 30 => 'Học đều 30 ngày',
            $__currentUser->streak >= 7 => 'Duy trì 7 ngày',
            $__currentUser->last_active_at?->gte(now()->subDays(7)) => 'Đang hoạt động',
            default => 'Bắt đầu học',
        };
    @endphp
    <div id="sidebar-header" x-data="{ open: false }" :class="{ 'is-open': open }" style="position:relative;">
        <div class="community-switcher-main">
            <button type="button" class="community-switcher-chevron" @click="open = !open" :aria-expanded="open.toString()" aria-controls="community-switcher-menu" aria-label="Đổi cộng đồng">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.25 15.75 12 19.5l3.75-3.75M8.25 8.25 12 4.5l3.75 3.75"/></svg>
            </button>
            <button type="button" class="community-switcher-button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="community-switcher-menu">
                @if($__isDiscoveryContext)
                    <span class="community-switcher-logo community-switcher-discovery-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></svg>
                    </span>
                    <span class="community-switcher-discovery-label" style="min-width:0;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:800;">Khám phá cộng đồng</span>
                @else
                    @if(($brand->slug ?? null) !== 'dscons' && $brand->logo_path)
                        <img src="{{ asset('storage/'.$brand->logo_path) }}" alt="" class="community-switcher-logo">
                    @else
                        <img src="{{ asset('1024x1024-da xoa nen.png') }}" alt="DSCons" class="community-switcher-logo" style="object-fit:contain;padding:2px;">
                    @endif
                    <span style="min-width:0;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:14px;font-weight:800;">{{ $brand->name }}</span>
                    @if($brand->isVerified())<span title="Đã xác minh" style="color:var(--green);font-size:12px;">✓</span>@endif
                @endif
            </button>
            @auth
                <livewire:notification-bell :key="'sidebar-notifications-'.$brand->id" />
            @endauth
        </div>
        <div id="community-switcher-menu" class="community-switcher-menu" x-cloak x-show="open" x-transition.origin.top @click.outside="open = false">
            @auth
                @forelse($__sidebarMemberships as $__membership)
                    @php
                        $__community = $__membership->brand;
                    @endphp
                    <a href="{{ route('community.feed', ['community' => $__community->slug]) }}" class="community-switcher-item {{ $__community->id === $brand->id ? 'active' : '' }}">
                        @if(($__community->slug ?? null) === 'dscons')<img src="{{ asset('1024x1024-da xoa nen.png') }}" alt="DSCons" class="community-switcher-logo" style="object-fit:contain;padding:2px;">@elseif($__community->logo_path)<img src="{{ asset('storage/'.$__community->logo_path) }}" alt="" class="community-switcher-logo">@else<span class="community-switcher-logo" style="display:grid;place-items:center;font-size:12px;font-weight:800;color:var(--green);">{{ strtoupper(substr($__community->name,0,1)) }}</span>@endif
                        <span style="min-width:0;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $__community->name }}</span>
                        @if($__community->isVerified())<span style="color:var(--green);font-size:12px;">✓</span>@endif
                    </a>
                @empty
                    <a href="{{ route('community.preview', $brand->slug) }}" class="community-switcher-item active">{{ $brand->name }}</a>
                @endforelse
            @else
                <a href="{{ route('community.preview', $brand->slug) }}" class="community-switcher-item active">{{ $brand->name }}</a>
            @endauth
            <div class="community-switcher-footer">
                <a href="{{ route('communities') }}">Khám phá cộng đồng</a>
                @auth<a href="{{ route('community.create') }}">Tạo cộng đồng</a>@else<a href="{{ route('login') }}">Đăng nhập</a>@endauth
            </div>
        </div>
    </div>

    <div id="channel-list">
        <div class="ch-category">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
            <x-icon name="users" size="14" color="var(--text-muted)" /> Cộng đồng
        </div>
        <a href="{{ community_route('feed') }}" class="ch-item {{ request()->routeIs('feed') || request()->routeIs('community.feed') ? 'active' : '' }}">
            <x-icon name="chat" size="18" />
            <span class="ch-name">Bảng tin</span>
        </a>
        <a href="{{ community_route('cot') }}" class="ch-item {{ request()->routeIs('cot') || request()->routeIs('community.cot') ? 'active' : '' }}">
            <x-icon name="compass" size="18" />
            <span class="ch-name">Kiến thức cốt lõi</span>
        </a>
        <a href="{{ community_route('qa') }}" class="ch-item {{ request()->routeIs('qa') || request()->routeIs('community.qa') ? 'active' : '' }}">
            <x-icon name="question" size="18" />
            <span class="ch-name">Hỏi đáp kỹ thuật</span>
        </a>

        <div class="ch-category">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
            <x-icon name="book" size="14" color="var(--text-muted)" /> Học tập
        </div>
        <a href="{{ community_route('academy') }}" class="ch-item {{ request()->routeIs('academy*') || request()->routeIs('community.academy*') ? 'active' : '' }}">
            <x-icon name="graduation" size="18" />
            <span class="ch-name">Khóa học</span>
        </a>
        <a href="{{ community_route('challenge') }}" class="ch-item {{ request()->routeIs('challenge*') || request()->routeIs('community.challenge*') ? 'active' : '' }}">
            <x-icon name="target" size="18" />
            <span class="ch-name">Challenge</span>
        </a>
        <a href="{{ community_route('events') }}" class="ch-item {{ request()->routeIs('events') || request()->routeIs('community.events') ? 'active' : '' }}">
            <x-icon name="calendar" size="18" />
            <span class="ch-name">Sự kiện</span>
        </a>
        <a href="{{ community_route('leaderboard') }}" class="ch-item {{ request()->routeIs('leaderboard') || request()->routeIs('community.leaderboard') ? 'active' : '' }}">
            <x-icon name="chart" size="18" />
            <span class="ch-name">Bảng xếp hạng</span>
        </a>

        <div class="ch-category">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
            <x-icon name="layers" size="14" color="var(--text-muted)" /> Tài nguyên
        </div>
        <a href="{{ community_route('marketplace') }}" class="ch-item {{ request()->routeIs('marketplace') || request()->routeIs('community.marketplace') ? 'active' : '' }}">
            <x-icon name="shopping-cart" size="18" />
            <span class="ch-name">Marketplace</span>
        </a>
        <a href="{{ community_route('orders') }}" class="ch-item {{ request()->routeIs('orders') || request()->routeIs('community.orders') ? 'active' : '' }}">
            <x-icon name="receipt" size="18" />
            <span class="ch-name">Gói &amp; Đơn hàng</span>
        </a>
        <a href="{{ community_route('affiliate') }}" class="ch-item {{ request()->routeIs('affiliate') || request()->routeIs('community.affiliate') ? 'active' : '' }}">
            <x-icon name="user-plus" size="18" />
            <span class="ch-name">Affiliate</span>
        </a>

        @auth
        @if(auth()->user()->isEngineer() && $brand->has_cv)
        <div class="ch-category"><x-icon name="file-text" size="14" color="var(--text-muted)" /> Hồ sơ nghề nghiệp</div>
        <a href="{{ community_route('engineer.cv') }}" class="ch-item {{ request()->routeIs('engineer.cv') || request()->routeIs('community.engineer.cv') ? 'active' : '' }}">
            <x-icon name="cv" size="18" /><span class="ch-name">CV của tôi</span>
        </a>
        <a href="{{ community_route('engineer.recruitment-requests') }}" class="ch-item {{ request()->routeIs('engineer.recruitment-requests') || request()->routeIs('community.engineer.recruitment-requests') ? 'active' : '' }}">
            <x-icon name="briefcase" size="18" /><span class="ch-name">Yêu cầu tuyển dụng</span>
        </a>
        @endif
        @endauth
        @can('admin')
        <div class="ch-category">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
            <x-icon name="settings" size="14" color="var(--text-muted)" /> Admin
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
            <button type="submit" class="panel-btn" title="Đăng xuất" aria-label="Đăng xuất">
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
        <button id="mobile-menu-trigger" type="button" class="mob-menu-btn" onclick="openSidebar()" aria-label="Mở menu" aria-controls="mob-sidebar" aria-expanded="false">
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
            @php
                $__topbarIcon = match (true) {
                    request()->routeIs('academy*', 'community.academy*') => 'graduation',
                    request()->routeIs('challenge*', 'community.challenge*') => 'target',
                    request()->routeIs('events', 'community.events') => 'calendar',
                    request()->routeIs('leaderboard', 'community.leaderboard') => 'trophy',
                    request()->routeIs('marketplace', 'community.marketplace') => 'shopping-cart',
                    request()->routeIs('orders', 'community.orders') => 'receipt',
                    request()->routeIs('cot', 'community.cot') => 'compass',
                    request()->routeIs('qa', 'community.qa') => 'question',
                    request()->routeIs('affiliate', 'community.affiliate') => 'user-plus',
                    default => 'chat',
                };
            @endphp
            <x-icon :name="$__topbarIcon" size="17" color="var(--green)" />
            {{ $title ?? 'Feed' }}
        </div>
        <div class="topbar-right">
            @auth
            <livewire:notification-bell />
            <a href="{{ route('messages') }}" class="topbar-btn" title="Tin nhắn" aria-label="Mở tin nhắn">
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
                @if(request()->routeIs('communities'))
                <div class="rp-card" style="padding:16px;">
                    <div style="width:36px;height:36px;display:grid;place-items:center;border-radius:11px;background:#E1F4F7;color:var(--green);margin-bottom:11px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:19px;height:19px;"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></svg></div>
                    <div style="font-size:15px;font-weight:800;color:var(--text);">Khám phá cộng đồng</div>
                    <p style="margin:6px 0 14px;color:var(--text-muted);font-size:12px;line-height:1.55;">Tìm nơi phù hợp để học tập, kết nối và cùng phát triển.</p>
                    <div style="display:grid;gap:8px;color:var(--text-muted);font-size:12px;"><div style="display:flex;gap:8px;"><strong style="display:grid;place-items:center;width:20px;height:20px;border-radius:50%;background:#E1F4F7;color:var(--green);font-size:11px;">1</strong><span><b style="color:var(--text);">Khám phá</b> cộng đồng phù hợp</span></div><div style="display:flex;gap:8px;"><strong style="display:grid;place-items:center;width:20px;height:20px;border-radius:50%;background:#E1F4F7;color:var(--green);font-size:11px;">2</strong><span><b style="color:var(--text);">Tham gia</b> để vào bảng tin</span></div></div>
                    @auth
                        <a href="{{ route('community.create') }}" class="ds-btn membership-cta" style="margin-top:15px;text-decoration:none;min-height:39px;padding:.5rem;">Tạo cộng đồng</a>
                    @else
                        <a href="{{ route('login') }}" class="ds-btn membership-cta" style="margin-top:15px;text-decoration:none;min-height:39px;padding:.5rem;">Đăng nhập để tham gia</a>
                    @endauth
                </div>
                @auth
                    @if($__discoveryMemberships->isNotEmpty())
                    <div class="rp-card" style="padding:14px;">
                        <div class="rp-card-header"><div class="rp-card-title">Cộng đồng của bạn</div></div>
                        @foreach($__discoveryMemberships->take(4) as $__discoveryMembership)
                            @php $__discoveryBrand = $__discoveryMembership->brand; @endphp
                            <a href="{{ route('community.feed', ['community' => $__discoveryBrand->slug]) }}" style="display:flex;align-items:center;gap:8px;padding:7px 0;text-decoration:none;color:var(--text);">
                                @if(($__discoveryBrand->slug ?? null) === 'dscons')<img src="{{ asset('1024x1024-da xoa nen.png') }}" alt="DSCons" style="width:28px;height:28px;object-fit:contain;padding:2px;border-radius:8px;background:#E1F4F7;">@elseif($__discoveryBrand->logo_path)<img src="{{ asset('storage/'.$__discoveryBrand->logo_path) }}" alt="" style="width:28px;height:28px;object-fit:cover;border-radius:8px;">@else<span style="width:28px;height:28px;display:grid;place-items:center;border-radius:8px;background:#E1F4F7;color:var(--green);font-size:12px;font-weight:800;">{{ strtoupper(substr($__discoveryBrand->name, 0, 1)) }}</span>@endif
                                <span style="min-width:0;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px;font-weight:700;">{{ $__discoveryBrand->name }}</span><span style="color:var(--green);font-size:12px;">→</span>
                            </a>
                        @endforeach
                    </div>
                    @endif
                @endauth
                @else
                @auth
                {{-- Membership and community information share one right-panel card. --}}
                <div class="rp-card community-summary-card" style="padding:0;overflow:hidden;flex:0 0 auto;">
                <div class="community-membership-panel" style="border-bottom:1px solid #D9E6EC;">
                    <div style="aspect-ratio:16/9;background:#125A96;position:relative;overflow:hidden;">
                        @if($brand->banner_path)
                            <img src="{{ asset('storage/'.$brand->banner_path) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                        @elseif($__featuredUpgrade?->thumbnail)
                            <img src="{{ asset('storage/'.$__featuredUpgrade->thumbnail) }}" alt="" style="width:100%;height:100%;object-fit:cover;opacity:.42;">
                        @else
                            <div class="rp-hero-fallback" aria-hidden="true">
                                <div class="rp-hero-fallback-inner"><x-icon name="layers" size="30" color="#fff" /><strong>{{ $brand->name }}</strong></div>
                            </div>
                        @endif
                        <div style="position:absolute;inset:0;background:rgba(6,39,62,.42);"></div>
                        <span style="position:absolute;left:14px;bottom:12px;font-size:10px;color:#fff;font-weight:800;letter-spacing:.12em;text-transform:uppercase;">THÀNH VIÊN DSCons</span>
                    </div>
                    <div style="padding:14px;">
                        <div style="font-size:16px;font-weight:800;letter-spacing:-.02em;color:var(--text);">Quyền lợi thành viên</div>
                        <p style="font-size:12px;color:var(--text-muted);line-height:1.5;margin:4px 0 12px;">Mở khóa toàn bộ challenge và khóa học trong cộng đồng.</p>
                        <a href="{{ community_route('membership') }}" class="ds-btn membership-cta" style="text-align:center;text-decoration:none;min-height:40px;padding:.55rem;">@if($__isPremiumMember)<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>Đã kích hoạt @else Xem quyền lợi thành viên @endif</a>
                    </div>
                </div>
                {{-- Current community information --}}
                <div class="community-info-panel" style="padding:15px;">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                        <div class="community-brand-mark" style="width:48px;height:48px;min-width:48px;max-width:48px;min-height:48px;max-height:48px;flex:0 0 48px;display:grid;place-items:center;overflow:hidden;border-radius:50%;">
                            @if(($brand->slug ?? null) === 'dscons')<img src="{{ asset('1024x1024-da xoa nen.png') }}" alt="DSCons" style="display:block;width:100%;height:100%;max-width:100%;max-height:100%;padding:4px;box-sizing:border-box;border-radius:50%;object-fit:contain;">@elseif($brand->logo_path)<img src="{{ asset('storage/'.$brand->logo_path) }}" alt="{{ $brand->name }}" style="display:block;width:100%;height:100%;max-width:100%;max-height:100%;border-radius:50%;object-fit:cover;">@else{{ strtoupper(substr($brand->name,0,1)) }}@endif
                        </div>
                        <div style="min-width:0;flex:1;"><div style="display:flex;gap:5px;align-items:center;font-size:14px;font-weight:800;color:var(--text);">{{ $brand->name }} @if($brand->isVerified())<span style="color:var(--green);font-size:12px;">✓</span>@endif</div><div style="font-size:11px;color:var(--text-muted);">/c/{{ $brand->slug }}</div></div>
                    </div>
                    <p style="font-size:12px;line-height:1.5;color:var(--text-muted);margin:0 0 11px;">{{ $brand->description ?: ($brand->tagline ?: 'Cộng đồng học tập DSCons.') }}</p>
                    <div class="community-stat-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:4px;padding:10px 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border);text-align:center;"><div><strong style="display:block;font-size:15px;color:var(--text);">{{ number_format($__communityMemberCount) }}</strong><span>thành viên</span></div><div><strong style="display:block;font-size:15px;color:var(--text);">{{ number_format($__communityActiveCount) }}</strong><span>online</span></div><div><strong style="display:block;font-size:15px;color:var(--text);">{{ number_format($__communityAdminCount) }}</strong><span>quản trị</span></div></div>
                    <div style="display:flex;align-items:center;justify-content:flex-start;flex-wrap:nowrap;gap:4px;margin-top:11px;min-width:0;overflow:visible;"><div class="community-member-avatars" style="--community-avatar-size:{{ $__memberAvatarSize }}px;display:flex;align-items:center;flex-wrap:nowrap;white-space:nowrap;flex:1 1 auto;min-width:0;min-height:{{ $__memberAvatarSize }}px;padding-left:10px;overflow:visible;">@foreach($__communityMembers->take(5) as $__member)<img src="{{ $__member->avatar_url }}" alt="Ảnh đại diện thành viên {{ $__member->name }}" style="display:block;width:{{ $__memberAvatarSize }}px;height:{{ $__memberAvatarSize }}px;min-width:{{ $__memberAvatarSize }}px;max-width:{{ $__memberAvatarSize }}px;min-height:{{ $__memberAvatarSize }}px;max-height:{{ $__memberAvatarSize }}px;flex:0 0 {{ $__memberAvatarSize }}px;margin-left:calc({{ $__memberAvatarSize }}px / -3.6);border:2px solid #fff;border-radius:50%;object-fit:cover;">@endforeach</div></div>
                    <div class="community-member-status" style="display:grid;gap:5px;margin-top:10px;padding:7px 9px;border:1px solid #E4B83A;border-radius:10px;background:#FFFDF4;">
                        @if($__isPremiumMember)
                            <div class="community-member-status-title" style="display:flex;align-items:center;gap:6px;color:#8A5A00;font-size:11px;line-height:1.35;"><x-icon name="trophy" size="15" color="#A16207" /><span>Thành viên</span><strong style="color:#6F4300;font-size:11px;font-weight:800;letter-spacing:.015em;">{{ $__membershipDisplayLabel }}</strong></div>
                        @elseif($__isCommunityParticipant)
                            <div class="community-member-status-title is-community"><span>Thành viên cộng đồng</span></div>
                        @else
                            <div class="community-member-status-title is-community"><span>Chưa tham gia</span></div>
                        @endif
                        <div class="community-member-status-details" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:5px 8px;padding-top:5px;border-top:1px solid rgba(228,184,58,.32);font-size:10px;line-height:1.3;"><span style="display:inline-flex;align-items:center;gap:4px;min-width:0;"><small style="color:#61798A;font-size:10px;">Điểm học tập</small><strong style="color:#125A96;font-size:10px;font-weight:800;white-space:nowrap;">{{ number_format(auth()->user()->xp) }} EXP</strong></span><span style="display:inline-flex;align-items:center;gap:4px;min-width:0;"><x-icon name="bolt" size="13" color="#1F77BE" /><em style="color:#1F77BE;font-size:10px;font-style:italic;font-weight:700;white-space:nowrap;">{{ $__activityLabel }}</em></span></div>
                    </div>
                    <button type="button" onclick="copyCommunityLink('{{ route('community.preview', $brand->slug) }}')" class="ds-btn community-invite-button" style="width:100%;margin-top:12px;min-height:39px;padding:.5rem;">Mời bạn bè</button>
                </div>

                </div>

                {{-- Community leaderboard --}}
                <livewire:sidebar-leaderboard />
                <livewire:feedback-button :key="'feedback-desktop-'.$brand->id" />
                @endauth
                @endif
            </div>
        </div>

    </div>
</div>

{{-- MOBILE: Overlay + sidebar ───────────────── --}}
<div id="mob-overlay" onclick="closeSidebar()" aria-hidden="true"></div>
<aside id="mob-sidebar" role="navigation" aria-label="Điều hướng cộng đồng" aria-hidden="true" inert>
    <div class="mobile-sidebar-header">
        <span style="display:flex;align-items:center;gap:8px;min-width:0;flex:1;">
            @if($__isDiscoveryContext)
                <span aria-hidden="true" style="width:28px;height:28px;display:grid;place-items:center;border-radius:8px;background:#E1F4F7;color:var(--green);flex:0 0 auto;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 1 0 0 18M12 3a14 14 0 1 1 0 18"/></svg></span>
            @elseif(($brand->slug ?? null) === 'dscons')
                <img src="{{ asset('1024x1024-da xoa nen.png') }}" alt="DSCons" style="width:32px;height:32px;object-fit:contain;padding:2px;border-radius:9px;background:#E1F4F7;flex:0 0 auto;">
            @elseif($brand->logo_path)
                <img src="{{ asset('storage/'.$brand->logo_path) }}" alt="{{ $brand->name }}" style="width:32px;height:32px;object-fit:cover;border-radius:9px;flex:0 0 auto;">
            @else
                <span aria-hidden="true" style="width:32px;height:32px;display:grid;place-items:center;border-radius:9px;background:#E1F4F7;color:var(--green);font-size:13px;font-weight:800;flex:0 0 auto;">{{ strtoupper(substr($brand->name,0,1)) }}</span>
            @endif
            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:14px;font-weight:750;">{{ $__isDiscoveryContext ? 'Khám phá cộng đồng' : $brand->name }}</span>
            @if(!$__isDiscoveryContext && $brand->isVerified())<span title="Đã xác minh" style="color:var(--green);">✓</span>@endif
        </span>
        <button type="button" class="panel-btn" onclick="closeSidebar()" aria-label="Đóng menu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
    </div>
    <div style="padding:10px 12px 4px;display:flex;gap:8px;border-bottom:1px solid var(--border);"><a href="{{ route('communities') }}" style="font-size:12px;color:var(--green);text-decoration:none;">Đổi cộng đồng</a>@auth<a href="{{ route('community.create') }}" style="font-size:12px;color:var(--text-muted);text-decoration:none;">Tạo mới</a>@endauth</div>
    @auth
    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:6px;padding:10px 12px;border-bottom:1px solid var(--border);">
        <a href="{{ community_route('membership') }}" style="display:grid;justify-items:center;gap:4px;padding:8px 4px;border-radius:9px;background:#EEF7F9;color:var(--text);font-size:10px;font-weight:750;text-decoration:none;">Membership</a>
        <a href="{{ community_route('leaderboard') }}" style="display:grid;justify-items:center;gap:4px;padding:8px 4px;border-radius:9px;background:#EEF7F9;color:var(--text);font-size:10px;font-weight:750;text-decoration:none;">Xếp hạng</a>
        <a href="{{ community_route('events') }}" style="display:grid;justify-items:center;gap:4px;padding:8px 4px;border-radius:9px;background:#EEF7F9;color:var(--text);font-size:10px;font-weight:750;text-decoration:none;">Sự kiện</a>
    </div>
    @endauth
    <div id="channel-list" style="flex:1;overflow-y:auto;padding:6px;">
        <div class="ch-category"><x-icon name="users" size="14" color="var(--text-muted)" /> {{ $__isDiscoveryContext ? 'Cộng đồng' : $brand->name }}</div>
        <a href="{{ community_route('feed') }}" class="ch-item {{ request()->routeIs('feed') || request()->routeIs('community.feed')?'active':'' }}" onclick="closeSidebar()">
            <x-icon name="chat" size="18" />
            <span class="ch-name">Bảng tin</span>
        </a>
        <a href="{{ community_route('cot') }}" class="ch-item {{ request()->routeIs('cot') || request()->routeIs('community.cot')?'active':'' }}" onclick="closeSidebar()">
            <x-icon name="compass" size="18" />
            <span class="ch-name">Kiến thức cốt lõi</span>
        </a>
        <a href="{{ community_route('qa') }}" class="ch-item {{ request()->routeIs('qa') || request()->routeIs('community.qa')?'active':'' }}" onclick="closeSidebar()">
            <x-icon name="question" size="18" />
            <span class="ch-name">Hỏi đáp kỹ thuật</span>
        </a>
        <div class="ch-category"><x-icon name="book" size="14" color="var(--text-muted)" /> Học tập</div>
        <a href="{{ community_route('academy') }}" class="ch-item {{ request()->routeIs('academy*') || request()->routeIs('community.academy*')?'active':'' }}" onclick="closeSidebar()">
            <x-icon name="graduation" size="18" />
            <span class="ch-name">Khóa học</span>
        </a>
        <a href="{{ community_route('challenge') }}" class="ch-item {{ request()->routeIs('challenge*') || request()->routeIs('community.challenge*')?'active':'' }}" onclick="closeSidebar()">
            <x-icon name="target" size="18" />
            <span class="ch-name">Challenge</span>
        </a>
        <a href="{{ community_route('events') }}" class="ch-item {{ request()->routeIs('events') || request()->routeIs('community.events')?'active':'' }}" onclick="closeSidebar()">
            <x-icon name="calendar" size="18" />
            <span class="ch-name">Sự kiện</span>
        </a>
        <a href="{{ community_route('leaderboard') }}" class="ch-item {{ request()->routeIs('leaderboard') || request()->routeIs('community.leaderboard')?'active':'' }}" onclick="closeSidebar()">
            <x-icon name="chart" size="18" />
            <span class="ch-name">Bảng xếp hạng</span>
        </a>
        <div class="ch-category"><x-icon name="layers" size="14" color="var(--text-muted)" /> Tài nguyên</div>
        <a href="{{ community_route('marketplace') }}" class="ch-item {{ request()->routeIs('marketplace') || request()->routeIs('community.marketplace')?'active':'' }}" onclick="closeSidebar()">
            <x-icon name="shopping-cart" size="18" />
            <span class="ch-name">Marketplace</span>
        </a>
        <a href="{{ community_route('orders') }}" class="ch-item {{ request()->routeIs('orders') || request()->routeIs('community.orders')?'active':'' }}" onclick="closeSidebar()">
            <x-icon name="receipt" size="18" />
            <span class="ch-name">Gói &amp; Đơn hàng</span>
        </a>
        <a href="{{ community_route('affiliate') }}" class="ch-item {{ request()->routeIs('affiliate') || request()->routeIs('community.affiliate')?'active':'' }}" onclick="closeSidebar()">
            <x-icon name="user-plus" size="18" />
            <span class="ch-name">Affiliate</span>
        </a>
        @auth
        @if(auth()->user()->isEngineer() && $brand->has_cv)
        <div class="ch-category"><x-icon name="file-text" size="14" color="var(--text-muted)" /> Hồ sơ nghề nghiệp</div>
        <a href="{{ community_route('engineer.cv') }}" class="ch-item {{ request()->routeIs('engineer.cv') || request()->routeIs('community.engineer.cv')?'active':'' }}" onclick="closeSidebar()"><x-icon name="cv" size="18" /><span class="ch-name">CV của tôi</span></a>
        <a href="{{ community_route('engineer.recruitment-requests') }}" class="ch-item {{ request()->routeIs('engineer.recruitment-requests') || request()->routeIs('community.engineer.recruitment-requests')?'active':'' }}" onclick="closeSidebar()"><x-icon name="briefcase" size="18" /><span class="ch-name">Yêu cầu tuyển dụng</span></a>
        @endif
        @endauth
        @can('admin')
        <a href="{{ route('admin.dashboard') }}" class="ch-item {{ request()->routeIs('admin.*')?'active':'' }}" onclick="closeSidebar()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 01-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
            <span class="ch-name">Admin</span>
        </a>
        @endcan
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
</aside>

{{-- MOBILE: Bottom nav ───────────────────────── --}}
@auth
<nav id="mobile-nav">
    <a href="{{ community_route('feed') }}" class="mob-nav-btn {{ request()->routeIs('feed') || request()->routeIs('community.feed')?'active':'' }}" aria-current="{{ request()->routeIs('feed') || request()->routeIs('community.feed') ? 'page' : 'false' }}">
        <x-icon name="home" size="18" />
        Home
    </a>
    <a href="{{ community_route('academy') }}" class="mob-nav-btn {{ request()->routeIs('academy*') || request()->routeIs('community.academy*')?'active':'' }}" aria-current="{{ request()->routeIs('academy*') || request()->routeIs('community.academy*') ? 'page' : 'false' }}">
        <x-icon name="graduation" size="18" />
        Học tập
    </a>
    <a href="{{ community_route('qa') }}" class="mob-nav-btn {{ request()->routeIs('qa') || request()->routeIs('community.qa')?'active':'' }}" aria-current="{{ request()->routeIs('qa') || request()->routeIs('community.qa') ? 'page' : 'false' }}">
        <x-icon name="question" size="18" />
        Hỏi đáp
    </a>
    <a href="{{ community_route('challenge') }}" class="mob-nav-btn {{ request()->routeIs('challenge*') || request()->routeIs('community.challenge*')?'active':'' }}" aria-current="{{ request()->routeIs('challenge*') || request()->routeIs('community.challenge*') ? 'page' : 'false' }}">
        <x-icon name="target" size="18" />
        Challenge
    </a>
    <a href="{{ route('profile', auth()->user()->username ?? auth()->id()) }}" class="mob-nav-btn {{ request()->routeIs('profile')?'active':'' }}" aria-current="{{ request()->routeIs('profile') ? 'page' : 'false' }}">
        <img src="{{ auth()->user()->avatar_url }}" style="width:22px;height:22px;border-radius:50%;object-fit:cover;" alt="">
        Tôi
    </a>
</nav>
@endauth

</div>{{-- /#app --}}

{{-- Mobile feedback stays available without covering the desktop leaderboard. --}}
@auth
<div class="mobile-feedback-host">
    <livewire:feedback-button :key="'feedback-mobile'" />
</div>
@endauth

{{-- Post Modal (global) --}}
@auth
<livewire:post-modal />
@endauth

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
function copyCommunityLink(url) {
    if (navigator.clipboard?.writeText) {
        navigator.clipboard.writeText(url);
    }
    window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Đã sao chép liên kết cộng đồng.', type: 'success' } }));
}
function openSidebar()  {
    const sidebar = document.getElementById('mob-sidebar');
    const overlay = document.getElementById('mob-overlay');
    const trigger = document.getElementById('mobile-menu-trigger');
    if (!sidebar || !overlay) return;

    sidebar.classList.add('open');
    sidebar.removeAttribute('inert');
    sidebar.setAttribute('aria-hidden', 'false');
    overlay.classList.add('open');
    trigger?.setAttribute('aria-expanded', 'true');

    requestAnimationFrame(() => {
        sidebar.querySelector('button, a, input, select, textarea, [tabindex]:not([tabindex="-1"])')?.focus();
    });
}
function closeSidebar() {
    const sidebar = document.getElementById('mob-sidebar');
    const overlay = document.getElementById('mob-overlay');
    const trigger = document.getElementById('mobile-menu-trigger');
    if (!sidebar || !overlay) return;

    sidebar.classList.remove('open');
    sidebar.setAttribute('inert', '');
    sidebar.setAttribute('aria-hidden', 'true');
    overlay.classList.remove('open');
    trigger?.setAttribute('aria-expanded', 'false');
    trigger?.focus();
}

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && document.getElementById('mob-sidebar')?.classList.contains('open')) {
        closeSidebar();
    }
});
</script>

@stack('scripts')
</body>
</html>
