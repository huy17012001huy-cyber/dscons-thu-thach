<style>
    .preview-page { max-width: 1040px; margin: 0 auto; padding: 26px 28px 64px; color: var(--text); }
    .preview-hero { position: relative; overflow: hidden; min-height: 242px; border: 1px solid #CFE1E8; border-radius: 20px; background: linear-gradient(135deg, {{ $community->theme_primary ?: '#1F77BE' }}, #0B4D76); box-shadow: 0 10px 26px rgba(18,59,89,.1); }
    .preview-hero-image { width: 100%; height: 100%; position: absolute; inset: 0; object-fit: cover; opacity: .48; }
    .preview-hero::after { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, rgba(5,34,53,.86), rgba(5,34,53,.35) 68%, rgba(5,34,53,.12)); }
    .preview-hero-content { position: relative; z-index: 1; display: flex; align-items: end; gap: 16px; min-height: 242px; padding: 26px 28px; color: #fff; }
    .preview-logo { width: 74px; height: 74px; flex: 0 0 auto; display: grid; place-items: center; overflow: hidden; border: 3px solid rgba(255,255,255,.9); border-radius: 20px; background: #fff; box-shadow: 0 8px 18px rgba(0,0,0,.2); }
    .preview-logo img { width: 100%; height: 100%; object-fit: contain; padding: 5px; }
    .preview-title { margin: 0; font-size: clamp(26px, 3vw, 34px); font-weight: 800; letter-spacing: -.045em; line-height: 1.1; }
    .preview-slug { margin-top: 6px; color: rgba(255,255,255,.82); font-size: 13px; }
    .preview-verified { color: #8DE2FF; font-size: 16px; }
    .preview-intro { display: flex; align-items: start; justify-content: space-between; gap: 22px; padding: 22px 24px; border: 1px solid #D5E4EA; border-top: 0; border-radius: 0 0 18px 18px; background: #fff; box-shadow: 0 4px 12px rgba(18,59,89,.04); }
    .preview-tagline { margin: 0 0 6px; color: var(--text); font-size: 17px; font-weight: 750; }
    .preview-description { max-width: 650px; margin: 0; color: var(--text-muted); font-size: 14px; line-height: 1.65; }
    .preview-primary { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 43px; padding: 10px 17px; border: 1px solid #1F77BE !important; border-radius: 12px !important; background: #1F77BE !important; color: #fff !important; font-weight: 800 !important; text-decoration: none; white-space: nowrap; box-shadow: 0 5px 13px rgba(31,119,190,.2); transition: transform .15s ease, background .15s ease, box-shadow .15s ease; }
    .preview-primary:hover { border-color: #125A96 !important; background: #125A96 !important; box-shadow: 0 8px 18px rgba(18,90,150,.25); transform: translateY(-1px); }
    .preview-primary:active { transform: translateY(0); }
    .preview-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin: 16px 0 24px; }
    .preview-stat { padding: 15px 16px; border: 1px solid #D7E5EA; border-radius: 14px; background: #fff; }
    .preview-stat-label { color: var(--text-muted); font-size: 11px; }
    .preview-stat-value { margin-top: 4px; color: var(--text); font-size: 22px; font-weight: 800; letter-spacing: -.03em; }
    .preview-section { margin-top: 24px; }
    .preview-section-heading { display: flex; align-items: end; justify-content: space-between; gap: 12px; margin-bottom: 11px; }
    .preview-section-heading h2 { margin: 0; color: var(--text); font-size: 19px; letter-spacing: -.025em; }
    .preview-section-heading span { color: var(--text-muted); font-size: 12px; }
    .preview-overview { padding: 20px 22px; border: 1px solid #D7E5EA; border-radius: 16px; background: #fff; color: var(--text-muted); font-size: 14px; line-height: 1.7; }
    .preview-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 13px; }
    .preview-content-card { overflow: hidden; border: 1px solid #D7E5EA; border-radius: 15px; background: #fff; transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease; }
    .preview-content-card:hover { border-color: #A9CAD7; box-shadow: 0 9px 20px rgba(18,59,89,.09); transform: translateY(-2px); }
    .preview-content-cover { height: 105px; overflow: hidden; background: linear-gradient(135deg, #E3F4F7, #B5DCE6); }
    .preview-content-cover img { width: 100%; height: 100%; object-fit: cover; }
    .preview-content-body { padding: 13px 14px 14px; }
    .preview-content-body h3 { margin: 0; color: var(--text); font-size: 15px; line-height: 1.35; }
    .preview-content-body p { display: -webkit-box; margin: 7px 0 11px; overflow: hidden; color: var(--text-muted); font-size: 12px; line-height: 1.55; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
    .preview-content-meta { color: var(--text-muted); font-size: 11px; }
    .preview-empty { padding: 22px; border: 1px dashed #BDD5DF; border-radius: 14px; background: #F8FCFD; color: var(--text-muted); font-size: 13px; }
    .preview-start { display: grid; grid-template-columns: repeat(3, 1fr); gap: 9px; padding: 18px; border: 1px solid #D7E5EA; border-radius: 16px; background: #fff; }
    .preview-start-item { display: flex; align-items: center; gap: 9px; padding: 10px; border-radius: 10px; background: #F2F9FA; color: var(--text); font-size: 12px; font-weight: 700; }
    .preview-start-item svg { width: 17px; height: 17px; flex: 0 0 auto; color: var(--green); }
    @media (max-width: 900px) { .preview-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 700px) { .preview-page { padding: 16px 14px 44px; } .preview-hero, .preview-hero-content { min-height: 220px; } .preview-hero-content { padding: 20px; } .preview-logo { width: 60px; height: 60px; border-radius: 16px; } .preview-intro { display: block; padding: 18px; } .preview-primary { width: 100%; margin-top: 15px; } .preview-stats { grid-template-columns: repeat(2, 1fr); } .preview-grid, .preview-start { grid-template-columns: 1fr; } }
</style>

<div class="preview-page">
    <section class="preview-hero">
        @if($community->banner_path)<img class="preview-hero-image" src="{{ asset('storage/'.$community->banner_path) }}" alt="">@endif
        <div class="preview-hero-content">
            <div class="preview-logo">
                @if(($community->slug ?? null) === 'dscons')<img src="{{ asset('1024x1024-da xoa nen.png') }}" alt="DSCons">@elseif($community->logo_path)<img src="{{ asset('storage/'.$community->logo_path) }}" alt="{{ $community->name }}">@else<span style="font-size:28px;font-weight:850;color:var(--green);">{{ strtoupper(substr($community->name, 0, 1)) }}</span>@endif
            </div>
            <div>
                <h1 class="preview-title">{{ $community->name }} @if($community->isVerified())<span class="preview-verified" title="Đã xác minh">✓</span>@endif</h1>
                <div class="preview-slug">/c/{{ $community->slug }}</div>
            </div>
        </div>
    </section>

    <section class="preview-intro">
        <div>
            <p class="preview-tagline">{{ $community->tagline ?: 'Một community để học tập và phát triển cùng nhau.' }}</p>
            <p class="preview-description">{{ $community->description ?: 'Khám phá nội dung thực chiến, những challenge mới và kết nối với các thành viên có cùng mục tiêu.' }}</p>
        </div>
        <div>
            @auth
                @if($isMember)
                    <a href="{{ route('community.feed', ['community' => $community->slug]) }}" class="preview-primary">Vào bảng tin <span aria-hidden="true">→</span></a>
                @else
                    <button wire:click="join" class="preview-primary">Tham gia Free <span aria-hidden="true">→</span></button>
                @endif
            @else
                <a href="{{ route('login') }}" class="preview-primary">Đăng nhập để tham gia <span aria-hidden="true">→</span></a>
            @endauth
        </div>
    </section>

    <div class="preview-stats">
        <div class="preview-stat"><div class="preview-stat-label">Thành viên</div><div class="preview-stat-value">{{ number_format($memberCount) }}</div></div>
        <div class="preview-stat"><div class="preview-stat-label">Khóa học</div><div class="preview-stat-value">{{ number_format($courseCount) }}</div></div>
        <div class="preview-stat"><div class="preview-stat-label">Challenge</div><div class="preview-stat-value">{{ number_format($challengeCount) }}</div></div>
        <div class="preview-stat"><div class="preview-stat-label">Quản trị viên</div><div class="preview-stat-value">{{ number_format($adminCount) }}</div></div>
    </div>

    <section class="preview-section">
        <div class="preview-section-heading"><h2>Về community</h2><span>{{ $eventCount }} sự kiện</span></div>
        <div class="preview-overview">{{ $community->description ?: 'Đây là không gian để thành viên học hỏi, thực hành và chia sẻ tiến độ cùng nhau.' }}</div>
    </section>

    <section class="preview-section">
        <div class="preview-section-heading"><h2>Khóa học nổi bật</h2><span>{{ $courseCount }} khóa học</span></div>
        @if($courses->isEmpty())
            <div class="preview-empty">Community chưa có khóa học được publish.</div>
        @else
            <div class="preview-grid">
                @foreach($courses as $course)
                    <article class="preview-content-card">
                        <div class="preview-content-cover">@if($course->thumbnail)<img src="{{ asset('storage/'.$course->thumbnail) }}" alt="">@endif</div>
                        <div class="preview-content-body"><h3>{{ $course->title }}</h3><p>{{ $course->description ?: 'Nội dung học tập thực chiến dành cho thành viên.' }}</p><div class="preview-content-meta">{{ ucfirst($course->difficulty ?: 'Thực chiến') }} · {{ $course->modules()->count() }} module</div></div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="preview-section">
        <div class="preview-section-heading"><h2>Challenge đang mở</h2><span>{{ $challengeCount }} challenge</span></div>
        @if($challenges->isEmpty())
            <div class="preview-empty">Community chưa có challenge đang mở.</div>
        @else
            <div class="preview-grid">
                @foreach($challenges as $challenge)
                    <article class="preview-content-card"><div class="preview-content-cover" style="background:linear-gradient(135deg,#0E527F,#1F77BE);display:grid;place-items:center;color:#fff;font-size:14px;font-weight:800;">{{ $challenge->required_days }} NGÀY</div><div class="preview-content-body"><h3>{{ $challenge->title }}</h3><p>{{ $challenge->description ?: 'Bắt đầu một lộ trình thực hành mới cùng community.' }}</p><div class="preview-content-meta">{{ ucfirst($challenge->difficulty ?: 'Thực chiến') }} · {{ $challenge->required_days }} ngày</div></div></article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="preview-section">
        <div class="preview-section-heading"><h2>Bắt đầu tại đây</h2></div>
        <div class="preview-start"><div class="preview-start-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Tham gia bảng tin</div><div class="preview-start-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>Chọn challenge đầu tiên</div><div class="preview-start-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19h16M4 5h16M7 5v14M17 5v14M10 9h4M10 13h4"/></svg>Khám phá khóa học</div></div>
    </section>
</div>
