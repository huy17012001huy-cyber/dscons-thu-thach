<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="color-scheme" content="light">
<title>Tính năng — taip.io</title>
<meta name="description" content="Toàn bộ tính năng của nền tảng The All In Plan — thử thách nhiều ngày, XP, quiz tương tác, cộng đồng, vote, quản trị.">
<link rel="icon" type="image/webp" href="{{ asset('favicon.webp') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;0,9..144,900;1,9..144,400&family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
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

html { scroll-behavior: smooth; background: var(--paper); }
body {
    background: var(--paper);
    color: var(--ink);
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, sans-serif;
    line-height: 1.6;
    -webkit-font-smoothing: antialiased;
}

main {
    max-width: 920px;
    margin: 0 auto;
    padding: 60px 32px 80px;
}

@media (max-width: 720px) { main { padding: 40px 20px 60px; } }

.hero {
    text-align: center;
    margin-bottom: 64px;
    padding-bottom: 40px;
    border-bottom: 1px solid var(--rule);
}
.hero .tag {
    display: inline-block;
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    letter-spacing: 0.18em;
    color: var(--accent);
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 14px;
}
.hero h1 {
    font-family: 'Fraunces', serif;
    font-size: clamp(36px, 6vw, 56px);
    font-weight: 700;
    line-height: 1.05;
    color: var(--ink);
    margin-bottom: 18px;
    letter-spacing: -0.02em;
}
.hero p {
    font-size: 17px;
    color: var(--ink-soft);
    max-width: 620px;
    margin: 0 auto;
    line-height: 1.6;
}
.hero .meta {
    margin-top: 22px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    color: var(--ink-mute);
    letter-spacing: 0.05em;
}

section {
    margin-bottom: 56px;
}
section h2 {
    font-family: 'Fraunces', serif;
    font-size: 28px;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 8px;
    display: flex;
    align-items: baseline;
    gap: 14px;
}
section h2 .num {
    font-family: 'JetBrains Mono', monospace;
    font-size: 13px;
    color: var(--accent);
    font-weight: 600;
    letter-spacing: 0.1em;
}
section .lead {
    font-size: 15px;
    color: var(--ink-soft);
    margin-bottom: 24px;
    line-height: 1.65;
}

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 14px;
}

.feature {
    background: white;
    padding: 22px 24px;
    border-radius: 12px;
    border: 1px solid var(--rule);
    box-shadow: var(--shadow);
}
.feature .label {
    font-family: 'JetBrains Mono', monospace;
    font-size: 10.5px;
    letter-spacing: 0.12em;
    color: var(--accent);
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 8px;
    display: inline-block;
}
.feature h3 {
    font-family: 'Fraunces', serif;
    font-size: 17px;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 8px;
    line-height: 1.3;
}
.feature p {
    font-size: 13.5px;
    color: var(--ink-soft);
    line-height: 1.55;
    margin: 4px 0;
}
.feature p strong { color: var(--ink); font-weight: 600; }
.feature ul {
    list-style: none;
    padding: 0;
    margin-top: 8px;
}
.feature li {
    font-size: 13px;
    color: var(--ink-soft);
    line-height: 1.55;
    padding: 3px 0 3px 16px;
    position: relative;
}
.feature li::before {
    content: '·';
    position: absolute;
    left: 4px;
    color: var(--accent);
    font-weight: 700;
    font-size: 16px;
    line-height: 1;
}
.feature.hi {
    background: linear-gradient(135deg, var(--gold-soft) 0%, #faf2e0 100%);
    border-color: rgba(184, 136, 45, 0.4);
}
.feature.hi .label { color: var(--gold); }
.feature.hi h3 { color: var(--gold); }
.feature.sage {
    background: var(--sage-soft);
    border-color: var(--sage);
}
.feature.sage .label { color: var(--sage); }
.feature.sage h3 { color: var(--sage); }

.tldr {
    background: var(--paper-warm);
    border-radius: 12px;
    padding: 24px 28px;
    border-left: 4px solid var(--accent);
    margin-bottom: 56px;
}
.tldr-label {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    letter-spacing: 0.15em;
    color: var(--accent);
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 10px;
}
.tldr p { font-size: 15px; color: var(--ink); line-height: 1.65; margin: 6px 0; }
.tldr strong { font-weight: 700; }

footer {
    text-align: center;
    margin-top: 80px;
    padding-top: 40px;
    border-top: 1px solid var(--rule);
    color: var(--ink-mute);
}
footer .quote {
    font-family: 'Fraunces', serif;
    font-style: italic;
    font-size: 17px;
    color: var(--ink-soft);
    margin-bottom: 16px;
    line-height: 1.5;
}
footer .sig {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    letter-spacing: 0.1em;
}
footer a { color: var(--accent); text-decoration: none; font-weight: 600; }
footer a:hover { text-decoration: underline; }
</style>
</head>
<body>

<main>

<header class="hero">
    <div class="tag">All-features overview</div>
    <h1>Toàn bộ tính năng của taip.io</h1>
    <p>The All In Plan™ — nền tảng thử thách nhiều ngày dành cho marketer / entrepreneur Việt. Học, làm, nộp bài, vote, kiếm XP, leo Level — tất cả trong 1 nơi.</p>
    <div class="meta">Cập nhật {{ date('d/m/Y') }} · Internal use</div>
</header>

<div class="tldr">
    <div class="tldr-label">TL;DR — 30 giây</div>
    <p>Admin tạo thử thách trong <strong>/admin/challenges</strong>: đặt tên, số ngày, và nội dung từng nhiệm vụ. Member đăng ký tham gia, bấm "Bắt đầu" để khởi động đồng hồ cá nhân. Mỗi ngày unlock 1 nhiệm vụ — phải nộp bằng chứng + admin duyệt mới qua ngày kế. Có quiz tương tác, vote chéo, freeze để gia hạn, XP + Level cá nhân, leaderboard cộng đồng, và nhiều cơ chế chống gian lận.</p>
</div>

{{-- ── 1. THỬ THÁCH ───────────────────────────────── --}}
<section>
    <h2><span class="num">01</span>Thử thách nhiều ngày</h2>
    <p class="lead">Cốt lõi của nền tảng. Mỗi thử thách là 1 chuỗi nhiệm vụ ngày do admin cấu hình (số ngày tuỳ ý), có leader, members, deposit AIP, ngày bắt đầu/kết thúc.</p>
    <div class="grid">
        <div class="feature">
            <span class="label">Đăng ký · Duyệt</span>
            <h3>Flow tham gia 2 bước</h3>
            <p>Member bấm <strong>Đăng ký tham gia</strong> → trạng thái <em>pending</em>. Admin duyệt (hoặc reject). Sau khi được duyệt, member tự bấm <strong>Bắt đầu thử thách</strong> để khởi động đồng hồ cá nhân (`personal_starts_at`).</p>
        </div>
        <div class="feature">
            <span class="label">Đồng hồ cá nhân</span>
            <h3>Mỗi người 1 lịch riêng</h3>
            <p>Day 1 mở khi bấm Bắt đầu. Day N mở khi Day N-1 đã hoàn thành. Deadline tính theo <strong>giờ start cá nhân</strong> — không cố định 8h sáng cho mọi người.</p>
        </div>
        <div class="feature sage">
            <span class="label">Mới · Completion-gating</span>
            <h3>Không xong bài trước → khoá bài sau</h3>
            <p>Day N+1 chỉ unlock khi Day N có submission status ≠ rejected. Quá hạn cũng vẫn phải làm — không auto-skip. Members bị reject → coi như chưa làm, Day kế khoá lại.</p>
        </div>
        <div class="feature">
            <span class="label">Submission · Evidence</span>
            <h3>Nộp text + screenshot + link</h3>
            <p>Mỗi task quy định loại bằng chứng (`text` hoặc `screenshot`). Có thể bao gồm link Google Drive/Imgur, mô tả ngắn, hoặc cả hai. Admin xem trên dashboard để duyệt/reject với note giải thích.</p>
        </div>
        <div class="feature">
            <span class="label">Reject · Resubmit</span>
            <h3>Nộp lại không giới hạn</h3>
            <p>Bị reject → nộp lại tới khi được duyệt, kèm note giải thích của admin. Số lần bị từ chối lưu ở `reject_count` để admin nhìn ra ai đang chật vật.</p>
        </div>
        <div class="feature">
            <span class="label">Late tracking</span>
            <h3>Quá hạn không block — chỉ flag</h3>
            <p>Submit sau deadline → flag `is_late=true`, vẫn được nhận. Hiển thị badge "Hoàn thành (Trễ)". `consecutive_missed_days` ≥ 3 → cảnh báo qua notification.</p>
        </div>
        <div class="feature">
            <span class="label">Freeze · Tạm dừng</span>
            <h3>Đóng băng đồng hồ cả challenge</h3>
            <p>Admin có thể đóng băng từ Day X đến giờ Y. Trong window này, đồng hồ tất cả members tạm dừng — Day X+ ẩn, không tính trễ. Hệ thống auto-delay, không ai bị mất tiến độ.</p>
        </div>
        <div class="feature">
            <span class="label">Per-task deadline override</span>
            <h3>Gia hạn 1 ngày cụ thể</h3>
            <p>Admin set `deadline_override_at` cho 1 task → mỗi member tự động dời deadline về <strong>giờ start cá nhân</strong> trên ngày đã chọn. Cascade theo các day sau.</p>
        </div>
        <div class="feature">
            <span class="label">Vote chéo</span>
            <h3>Vote hay + Vote xuất sắc</h3>
            <p>Admin vote 2 loại độc lập trên submission của member: <strong>▲ Vote hay</strong> (xanh lá) và <strong>★ Vote xuất sắc</strong> (đỏ cam). Hiển thị count cho cả cộng đồng để học hỏi.</p>
        </div>
        <div class="feature">
            <span class="label">Contest mode</span>
            <h3>Nộp nhiều ứng dụng / 1 task</h3>
            <p>Bật `is_contest=true` → member nộp được nhiều bài cho cùng 1 task (vd: thi đấu app demo). XP chỉ cộng 1 lần, mỗi bài duyệt/reject độc lập.</p>
        </div>
    </div>
</section>

{{-- ── 2. QUIZ ───────────────────────────────── --}}
<section>
    <h2><span class="num">02</span>Trắc nghiệm tương tác</h2>
    <p class="lead">Quiz nhúng inline trong task page — không chuyển trang, không link out, mỗi câu đúng được +2 XP.</p>
    <div class="grid">
        <div class="feature hi">
            <span class="label">Cấu hình theo task</span>
            <h3>Bộ câu hỏi gắn vào từng nhiệm vụ</h3>
            <p>Admin dán JSON bộ câu hỏi vào task (`quiz_json`) — số câu tuỳ ý, mỗi câu đúng +2 XP. Render inline ngay trong trang nhiệm vụ với palette warm-paper.</p>
        </div>
        <div class="feature">
            <span class="label">Hỏi AI</span>
            <h3>Nút copy prompt cho mỗi câu</h3>
            <p>Mỗi câu có nút <strong>"Hỏi AI"</strong> — bấm copy prompt tình huống vào clipboard. Member dán vào Cursor/Antigravity/Claude Code, agent giải thích, hiểu sâu rồi mới chọn đáp án.</p>
        </div>
        <div class="feature">
            <span class="label">Click-to-commit</span>
            <h3>Chốt 1 lần, không retry</h3>
            <p>Click 1 đáp án → confirm → chốt vĩnh viễn. <strong>Không có nút "Kiểm tra"</strong>, không reveal đáp án rồi cho làm lại. Chống farm XP — chỉ +XP khi đúng ngay lần đầu.</p>
        </div>
        <div class="feature">
            <span class="label">State persist</span>
            <h3>Reload không mất tiến độ</h3>
            <p>Mỗi attempt lưu vào DB (`quiz_attempts`). Member quay lại trang vẫn thấy đáp án + giải thích cũ. Admin browse-as-member preview thì không lưu.</p>
        </div>
        <div class="feature">
            <span class="label">Admin authoring</span>
            <h3>Edit quiz JSON từ form</h3>
            <p>Admin sửa task → có textarea <strong>Quiz (JSON)</strong> với validate cấu trúc tự động (báo lỗi rõ field thiếu/sai). Có thể tái sử dụng cho task khác về sau.</p>
        </div>
        <div class="feature">
            <span class="label">Quiz report</span>
            <h3>Bảng điểm + correct rate</h3>
            <p>Trong /admin/challenges → bấm 📊 trên task có quiz → expand panel xem: per-question correct rate (highlight đỏ câu &lt;50%), bảng điểm từng user sorted by score.</p>
        </div>
    </div>
</section>

{{-- ── 3. XP & LEVEL ───────────────────────────────── --}}
<section>
    <h2><span class="num">03</span>XP, Level, Class</h2>
    <p class="lead">Hệ thống RPG-inspired. Engagement tạo ra XP, XP đẩy Level, Level mở khoá tính năng.</p>
    <div class="grid">
        <div class="feature">
            <span class="label">Engagement EXP</span>
            <h3>Người sáng tạo nhận EXP</h3>
            <p>Đăng bài = 0 EXP. Bài bạn được like/comment/bookmark thì <strong>BẠN</strong> nhận EXP. Comment hay (likes) cũng được EXP. Khuyến khích chất lượng nội dung, không spam số lượng.</p>
        </div>
        <div class="feature">
            <span class="label">Streak multiplier</span>
            <h3>Tích cực được nhân EXP</h3>
            <p>7 ngày streak → ×1.1 · 30 ngày → ×1.2 · 90 ngày → ×1.5. Áp cho mọi nguồn EXP. Streak break khi không login 1 ngày.</p>
        </div>
        <div class="feature">
            <span class="label">100 levels</span>
            <h3>Bảng EXP MapleStory-inspired</h3>
            <p>Level 1-10: vài trăm EXP. Level 50: ~60k. Level 100: hàng triệu. Cấp số nhân, càng cao càng khó. Hiện badge `Lv.X` cạnh tên member khắp nền tảng.</p>
        </div>
        <div class="feature">
            <span class="label">5 Class</span>
            <h3>Định danh archetype</h3>
            <p><strong>Offer Architect</strong> · <strong>Traffic Mage</strong> · <strong>Conversion Ranger</strong> · <strong>Delivery Assassin</strong> · <strong>Continuity Captain</strong>. Mỗi class có emoji + màu badge + thiên hướng task.</p>
        </div>
        <div class="feature">
            <span class="label">AIP currency</span>
            <h3>Aura In Progress</h3>
            <p>Currency phụ — kiếm qua action, dùng để deposit vào challenge, mua items trong marketplace, refund khi hoàn thành đúng tiến độ.</p>
        </div>
        <div class="feature">
            <span class="label">Đá Không Cực 💎</span>
            <h3>Reward hiếm</h3>
            <p>Loại đá quý "không cực" — drop hiếm khi đạt thành tích đặc biệt. Track riêng trong `da_khong_cuc` table. Hiển thị count cạnh avatar.</p>
        </div>
        <div class="feature">
            <span class="label">Power Symbols</span>
            <h3>Mảnh quyền năng theo trụ cột</h3>
            <p>Mỗi pillar (offer/traffic/conversion/delivery/continuity) có power symbol. Comment/like content thuộc pillar nào → nhặt fragment pillar đó. Đủ → upgrade.</p>
        </div>
    </div>
</section>

{{-- ── 4. CỘNG ĐỒNG ───────────────────────────────── --}}
<section>
    <h2><span class="num">04</span>Cộng đồng & Feed</h2>
    <p class="lead">Discord-like 3-column layout. Posts, comments, feed, signal, CỐT, Q&A.</p>
    <div class="grid">
        <div class="feature">
            <span class="label">5 trụ cột</span>
            <h3>Pillar system</h3>
            <p>Mỗi post thuộc 1 trụ cột: <strong>Offer</strong>, <strong>Traffic</strong>, <strong>Conversion</strong>, <strong>Delivery</strong>, <strong>Continuity</strong>. Filter feed theo pillar. Mỗi tuần 1 pillar được "Burning Zone" +50% EXP.</p>
        </div>
        <div class="feature">
            <span class="label">Topics</span>
            <h3>Format tag (admin-managed)</h3>
            <p>Case Study, Win, Hỏi đáp, Hành trình... Admin quản lý qua /admin/topics. Mỗi topic có emoji + slug + sort order. Member chọn 1 topic khi đăng bài.</p>
        </div>
        <div class="feature">
            <span class="label">Signal (≤500 words)</span>
            <h3>Tab feed riêng cho post ngắn</h3>
            <p>`is_signal=true` → hiện trong tab "Tín hiệu" tách riêng khỏi feed chính. Format quick share: 1 insight, 1 quote, 1 win nhỏ. Khuyến khích chia sẻ thường xuyên.</p>
        </div>
        <div class="feature">
            <span class="label">CỐT</span>
            <h3>Curated essential posts</h3>
            <p>Bài "cốt lõi" được nominate bởi user GD3+ (level ≥ 30). Hiển thị badge ★ CỐT, tách riêng tab. Admin review nominations qua /admin/cot-review.</p>
        </div>
        <div class="feature">
            <span class="label">Rune ✨</span>
            <h3>2× EXP cho first comment</h3>
            <p>Khi rune kích hoạt trên 1 post, comment đầu tiên trong window thời gian được nhân đôi EXP. Atomic claim — chỉ 1 user thắng. Banner đỏ cam hiện trên feed.</p>
        </div>
        <div class="feature">
            <span class="label">Tag @ user</span>
            <h3>Mention với typeahead</h3>
            <p>Gõ <code>@</code> trong comment → dropdown suggest user (filter theo username/name, ↑/↓ chọn). Sau khi chọn, render thành link xanh tới profile. User được tag nhận notification.</p>
        </div>
        <div class="feature">
            <span class="label">Like · Bookmark · Comment</span>
            <h3>Tương tác chuẩn social</h3>
            <p>Like = +2 EXP cho author. Comment = +3 EXP cho author lần đầu mỗi user. Bookmark = +1 EXP. Reply nested trong comment.</p>
        </div>
        <div class="feature">
            <span class="label">Q&A</span>
            <h3>Hỏi đáp với best answer</h3>
            <p>Hỏi → community trả lời → asker chọn best answer → người trả lời nhận +25 EXP. Tab Q&A riêng, search được.</p>
        </div>
    </div>
</section>

{{-- ── 5. NOTIFICATION & UX ───────────────────────────────── --}}
<section>
    <h2><span class="num">05</span>Notification & UX</h2>
    <p class="lead">Đẩy đúng thông tin đúng người, không spam.</p>
    <div class="grid">
        <div class="feature">
            <span class="label">Bell badge</span>
            <h3>Notification center top-right</h3>
            <p>Đếm unread real-time, badge đỏ. Click mở dropdown 320px hiện 10 notification gần nhất. Đánh dấu đã đọc bằng 1 nút. Click 1 noti → redirect tới context (post/task/profile).</p>
        </div>
        <div class="feature">
            <span class="label">7 loại noti</span>
            <h3>Coverage đầy đủ</h3>
            <p>Comment, like, mention, expedition events (duyệt/reject/late), CỐT nominate, Rune trigger, miss-day warning. Icon emoji + message Vietnamese.</p>
        </div>
        <div class="feature">
            <span class="label">Anti-spam</span>
            <h3>Rate limit + dedup</h3>
            <p>Comment ≤ 20/giờ/user. Notification dedup: post owner chỉ nhận noti lần đầu mỗi user comment, không repeat. Mention không tự notify chủ post (đã có noti riêng).</p>
        </div>
        <div class="feature">
            <span class="label">Auto-expand textarea</span>
            <h3>Input mượt như Discord</h3>
            <p>Textarea tự cao theo content, không scroll bar bên trong. Enter để submit, Shift+Enter xuống dòng. Paste markdown từ Notion/Word tự convert sang plain text.</p>
        </div>
        <div class="feature">
            <span class="label">Mobile responsive</span>
            <h3>Cùng UX, chỉnh layout</h3>
            <p>≥1200px: 3-column desktop. &lt;1200px: hamburger sidebar trượt từ trái, content full width. Bottom nav cố định cho mobile. Tap target ≥44px.</p>
        </div>
        <div class="feature">
            <span class="label">Toast</span>
            <h3>Feedback inline</h3>
            <p>Toast bottom-right cho mọi action (đăng bài, nộp xong, +XP). Auto-dismiss 4s. 3 type: success xanh, error đỏ, info trắng.</p>
        </div>
    </div>
</section>

{{-- ── 6. ADMIN ───────────────────────────────── --}}
<section>
    <h2><span class="num">06</span>Quản trị</h2>
    <p class="lead">Custom Livewire admin (không Filament). Mỗi feature có view riêng, gọn nhẹ.</p>
    <div class="grid">
        <div class="feature">
            <span class="label">/admin/challenges</span>
            <h3>CRUD expedition + tasks</h3>
            <p>Quản lý challenge, leader, capacity, deposit AIP. Thêm/sửa/xóa task per day với title, description (markdown), SOP, video URL, meeting time, evidence label, admin note, quiz JSON.</p>
        </div>
        <div class="feature">
            <span class="label">Review submissions</span>
            <h3>Duyệt/reject với note</h3>
            <p>Admin xem feed submissions theo task, search theo user/email/content. Approve/reject inline. Reject phải kèm note giải thích → user nhận noti có lý do.</p>
        </div>
        <div class="feature">
            <span class="label">Pending member</span>
            <h3>Approve/reject join requests</h3>
            <p>Trong panel challenge → list pending members. 1 click approve hoặc reject. Bulk-approve all pending. User nhận noti ngay.</p>
        </div>
        <div class="feature">
            <span class="label">Freeze · Override · Locked</span>
            <h3>3 cơ chế điều phối thời gian</h3>
            <p><strong>Freeze:</strong> đóng băng cả challenge. <strong>deadline_override_at:</strong> dời deadline 1 task per-member. <strong>locked_until:</strong> tạm khoá submit 1 task.</p>
        </div>
        <div class="feature sage">
            <span class="label">Mới · Preview as member</span>
            <h3>Admin xem như member</h3>
            <p>Admin chưa join challenge vẫn vào trang xem được như member: full task UI, quiz tương tác, evidence form. Banner vàng "🛡 Admin preview" nhắc rõ. Quiz/submit không lưu DB, không nhận XP.</p>
        </div>
        <div class="feature">
            <span class="label">Impersonate user</span>
            <h3>Login as bất kỳ user</h3>
            <p>Admin click "Login as" trong /admin/users → vào hệ thống với tư cách user đó. Banner đỏ trên cùng "Đang giả mạo X" + nút thoát. Debug user-specific issues nhanh.</p>
        </div>
        <div class="feature">
            <span class="label">/admin/topics, /courses, /cot-review</span>
            <h3>Quản lý content khác</h3>
            <p>Topics CRUD + sort order. Courses + Modules + Lessons + Tasks (academy). Review CỐT nominations. Báo cáo CRUD digital products + giao dịch.</p>
        </div>
        <div class="feature">
            <span class="label">XP transactions log</span>
            <h3>Truy vết mọi XP</h3>
            <p>Bảng `xp_transactions` ghi lại mọi lần award: amount, type, multiplier (streak), reference (post/task/comment), description. Audit + analytics.</p>
        </div>
    </div>
</section>

{{-- ── 7. KỸ THUẬT ───────────────────────────────── --}}
<section>
    <h2><span class="num">07</span>Kỹ thuật & UX nhỏ</h2>
    <p class="lead">Những thứ user không thấy nhưng cảm nhận được.</p>
    <div class="grid">
        <div class="feature">
            <span class="label">Stack</span>
            <h3>Laravel 12 · Livewire 3 · Tailwind v4</h3>
            <p>Server-rendered, Livewire reactive, Alpine.js cho client interactivity. Tailwind v4 với JIT. PostgreSQL 17 + JSONB. PHP 8.2+.</p>
        </div>
        <div class="feature">
            <span class="label">Anti-flicker</span>
            <h3>Tối ưu Macbook Retina</h3>
            <p>Bỏ <code>will-change</code> trên paint properties, CSS containment 3-column, font-display: optional, html bg match body. Tab-switch flicker giảm rõ rệt.</p>
        </div>
        <div class="feature">
            <span class="label">Multi-tenant</span>
            <h3>Brand-scoped data</h3>
            <p>Hệ thống đa thương hiệu — mỗi brand có theme riêng (color, logo), data isolation qua `brand_id`. User có thể follow nhiều brand, switch trên top bar.</p>
        </div>
        <div class="feature">
            <span class="label">Telegram integration</span>
            <h3>Notify admin khi có submission</h3>
            <p>Mỗi lần member nộp bài → bot Telegram nhắn admin chính kèm link review. Hỗ trợ duyệt nhanh trên di động không cần mở dashboard.</p>
        </div>
        <div class="feature">
            <span class="label">Sepay payment</span>
            <h3>VietQR phí tham gia</h3>
            <p>Thử thách có giá → member bấm đăng ký là ra QR VietQR sinh sẵn nội dung `CHAL{id}U{user}`. Trang tự poll 5s; webhook SePay xác nhận đủ tiền là duyệt luôn, không cần admin gật tay. Số tài khoản + API Key cấu hình ở <strong>/admin/settings</strong>.</p>
        </div>
        <div class="feature">
            <span class="label">Anti-cheat</span>
            <h3>XP exploit guards</h3>
            <p>Quiz: `xp_awarded` flag chống re-award. Vote: UNIQUE(completion, user, type) chống double-vote. Comment: rate limit 20/giờ. Membership: middleware check active.</p>
        </div>
    </div>
</section>

<footer>
    <p class="quote">"Một mình đi nhanh. Có cộng đồng đi xa. Có hệ thống — đi đúng."</p>
    <p class="sig">taip.io · The All In Plan™ · Internal feature recap · {{ date('Y') }}</p>
    <p class="sig" style="margin-top: 8px;"><a href="/login">Đăng nhập</a> · <a href="/feed">Vào nền tảng</a></p>
</footer>

</main>

</body>
</html>
