<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="color-scheme" content="light">
<meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
<title>Blog · Tài liệu nội bộ — taip.io</title>
<link rel="icon" type="image/webp" href="{{ asset('favicon.webp') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,400&family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root {
    --ink: #1a1612;
    --ink-soft: #4a4038;
    --ink-mute: #8a7f75;
    --paper: #faf7f2;
    --paper-warm: #f2ece0;
    --paper-deep: #ebe3d3;
    --rule: #d6cdbc;
    --accent: #c8441f;
    --accent-soft: #fce4dc;
    --gold: #b8882d;
    --gold-soft: #f8ecd0;
    --sage: #5c6b4d;
    --sage-soft: #e8ede0;
    --shadow: 0 1px 2px rgba(26,22,18,0.04), 0 8px 24px rgba(26,22,18,0.06);
}
html { background: var(--paper); }
body {
    background: var(--paper); color: var(--ink);
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, sans-serif;
    line-height: 1.6;
    -webkit-font-smoothing: antialiased;
}
main { max-width: 880px; margin: 0 auto; padding: 60px 32px 80px; }
@media (max-width: 720px) { main { padding: 40px 20px 60px; } }

.hero { text-align: center; margin-bottom: 56px; padding-bottom: 32px; border-bottom: 1px solid var(--rule); }
.hero .tag {
    display: inline-block;
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px; letter-spacing: 0.18em;
    color: var(--accent);
    text-transform: uppercase; font-weight: 600;
    margin-bottom: 14px;
}
.hero h1 {
    font-family: 'Fraunces', serif;
    font-size: clamp(34px, 5.5vw, 48px);
    font-weight: 700; line-height: 1.05;
    color: var(--ink); letter-spacing: -0.02em;
    margin-bottom: 14px;
}
.hero p { font-size: 15.5px; color: var(--ink-soft); max-width: 580px; margin: 0 auto; }
.hero .lock {
    margin-top: 18px;
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--paper-warm);
    border: 1px solid var(--rule);
    padding: 6px 14px; border-radius: 999px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px; letter-spacing: 0.04em;
    color: var(--ink-mute);
}

.section-label {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px; letter-spacing: 0.15em;
    color: var(--accent);
    text-transform: uppercase; font-weight: 600;
    margin: 32px 0 14px;
    padding-left: 14px;
    border-left: 3px solid var(--accent);
}

.post {
    background: white;
    padding: 24px 28px;
    margin-bottom: 14px;
    border-radius: 12px;
    border: 1px solid var(--rule);
    box-shadow: var(--shadow);
    transition: border-color 0.15s;
    text-decoration: none;
    color: inherit;
    display: block;
}
.post:hover {
    border-color: var(--accent);
}
.post .meta {
    display: flex; align-items: center; gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 10px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 10.5px; letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--ink-mute);
}
.post .meta .day-badge {
    background: var(--accent-soft);
    color: var(--accent);
    padding: 3px 10px;
    border-radius: 4px;
    font-weight: 700;
}
.post .meta .day-badge.gift {
    background: var(--gold-soft);
    color: var(--gold);
}
.post .meta .day-badge.general {
    background: var(--sage-soft);
    color: var(--sage);
}
.post h2 {
    font-family: 'Fraunces', serif;
    font-size: 22px;
    font-weight: 600;
    color: var(--ink);
    line-height: 1.3;
    margin-bottom: 8px;
}
.post p {
    font-size: 14px;
    color: var(--ink-soft);
    line-height: 1.55;
    margin-bottom: 12px;
}
.post .open {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px; letter-spacing: 0.1em;
    color: var(--accent);
    font-weight: 600;
    text-transform: uppercase;
}
.post:hover .open {
    text-decoration: underline;
}
@media (max-width: 720px) {
    .post { padding: 20px 22px; }
    .post h2 { font-size: 19px; }
}

footer {
    text-align: center;
    margin-top: 56px;
    padding-top: 32px;
    border-top: 1px solid var(--rule);
    color: var(--ink-mute);
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px; letter-spacing: 0.1em;
}
footer a { color: var(--accent); text-decoration: none; font-weight: 600; }
footer a:hover { text-decoration: underline; }
</style>
</head>
<body>

<main>

<header class="hero">
    <div class="tag">Internal blog</div>
    <h1>Tài liệu nội bộ</h1>
    <p>Tổng hợp guide chiến lược, hướng dẫn từng ngày, cẩm nang quản trị. Bookmark trang này để truy cập nhanh.</p>
    <div class="lock">🔒 chỉ thành viên có membership active mới đọc được — không share ra ngoài</div>
</header>

@php
    $posts = [
        [
            'group'      => 'general',
            'badge'      => 'tổng quan',
            'badgeClass' => 'general',
            'title'      => 'Iman Gadzhi — Sơ đồ toàn bộ kế hoạch bán hàng',
            'desc'       => 'Visual map đầy đủ funnel của Iman: từ free content → low-ticket offer → high-ticket coaching. Tham khảo chiến lược bán hàng cho business của bạn.',
            'href'       => '/iman-funnel-map-viet.html',
            'date'       => '13 / 04 / 2026',
        ],
        [
            'group'      => 'general',
            'badge'      => 'cẩm nang',
            'badgeClass' => 'gift',
            'title'      => 'GoClaw · Cẩm nang quản trị 11 menu',
            'desc'       => 'Tài liệu nội bộ chi tiết từng menu trong dashboard GoClaw — Knowledge Vault, Heartbeat, Skills, Tools, Logs... Quà tặng cho ai hoàn thành nhiệm vụ Ngày 13.',
            'href'       => '/bao-cao-huong-dan-quan-tri-menu.html',
            'date'       => '20 / 04 / 2026',
        ],
    ];
@endphp

<div class="section-label">Tổng quan & cẩm nang</div>
@foreach($posts as $p)
    @if($p['group'] === 'general')
    <a class="post" href="{{ $p['href'] }}">
        <div class="meta">
            <span class="day-badge {{ $p['badgeClass'] }}">{{ $p['badge'] }}</span>
            <span>{{ $p['date'] }}</span>
        </div>
        <h2>{{ $p['title'] }}</h2>
        <p>{{ $p['desc'] }}</p>
        <span class="open">Mở →</span>
    </a>
    @endif
@endforeach

<footer>
    <p>taip.io · Blog nội bộ · Cập nhật {{ date('d/m/Y') }}</p>
    <p style="margin-top: 8px;"><a href="/feed">← Về trang chính</a></p>
</footer>

</main>

</body>
</html>
