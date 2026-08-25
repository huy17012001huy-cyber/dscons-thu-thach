<div class="community-guide-page">
    <style>
        .community-guide-page { max-width:1080px; margin:0 auto; }
        .community-guide-hero { position:relative; isolation:isolate; display:grid; grid-template-columns:minmax(0,1fr) auto; align-items:center; gap:1.5rem; min-height:190px; padding:1.8rem 2rem; border-radius:22px; background:linear-gradient(125deg,#0E5D9B 0%,#1F77BE 68%,#2E8BCB 100%); color:#fff; overflow:hidden; box-shadow:0 16px 34px rgba(31,119,190,.2); }
        .community-guide-hero::before { content:""; position:absolute; z-index:-1; right:-50px; bottom:-100px; width:300px; height:300px; border:1px solid rgba(255,255,255,.24); border-radius:50%; box-shadow:0 0 0 30px rgba(255,255,255,.05),0 0 0 60px rgba(255,255,255,.04); }
        .community-guide-hero::after { content:""; position:absolute; z-index:-1; inset:0; opacity:.12; background-image:linear-gradient(rgba(255,255,255,.35) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.35) 1px,transparent 1px); background-size:28px 28px; mask-image:linear-gradient(110deg,black,transparent 72%); }
        .community-guide-kicker { display:flex; align-items:center; gap:.55rem; color:#DDF1FC; font-size:.7rem; font-weight:850; letter-spacing:.12em; text-transform:uppercase; }
        .community-guide-hero h1 { margin:.55rem 0 .45rem; color:#fff; font-size:clamp(1.65rem,3vw,2.35rem); letter-spacing:-.045em; line-height:1.08; }
        .community-guide-hero p { max-width:650px; margin:0; color:#E6F4FC; font-size:.88rem; line-height:1.65; }
        .community-guide-hero-stats { display:grid; gap:.6rem; min-width:150px; }
        .community-guide-stat { padding:.7rem .8rem; border:1px solid rgba(255,255,255,.25); border-radius:12px; background:rgba(255,255,255,.12); backdrop-filter:blur(8px); }
        .community-guide-stat strong { display:block; color:#fff; font-size:1rem; }
        .community-guide-stat span { display:block; margin-top:.15rem; color:#DDF1FC; font-size:.68rem; }
        .community-guide-section-head { display:flex; align-items:end; justify-content:space-between; gap:1rem; margin:1.45rem 0 .7rem; }
        .community-guide-section-head h2 { margin:0; color:#123B59; font-size:1.05rem; font-weight:800; letter-spacing:-.02em; }
        .community-guide-section-head p { margin:0; color:#61798A; font-size:.76rem; }
        .community-guide-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.9rem; }
        .community-guide-card { --tone:#1F77BE; position:relative; display:flex; min-height:190px; flex-direction:column; padding:1rem; border:1px solid #C9DFEA; border-radius:16px; background:#fff; color:inherit; text-decoration:none; box-shadow:0 4px 13px rgba(18,59,89,.045); overflow:hidden; animation:guide-card-in .45s both; animation-delay:calc(var(--index) * 45ms); transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease; }
        .community-guide-card::before { content:""; position:absolute; inset:0 0 auto; height:3px; background:var(--tone); }
        .community-guide-card:hover { transform:translateY(-4px); border-color:var(--tone); box-shadow:0 12px 25px rgba(18,59,89,.12); }
        .community-guide-card:focus-visible { outline:3px solid rgba(243,148,2,.55); outline-offset:3px; }
        .community-guide-card-top { display:flex; align-items:center; justify-content:space-between; gap:.7rem; }
        .community-guide-icon { display:grid; place-items:center; width:38px; height:38px; border-radius:12px; background:color-mix(in srgb,var(--tone) 11%,white); color:var(--tone); }
        .community-guide-number { color:#8AA4B4; font-size:.68rem; font-weight:850; }
        .community-guide-card h3 { margin:.8rem 0 .35rem; color:#123B59; font-size:.91rem; font-weight:800; line-height:1.25; }
        .community-guide-card-details { display:grid; gap:.32rem; margin:0; }
        .community-guide-card-details p { margin:0; color:#47657A; font-size:.76rem; line-height:1.6; overflow-wrap:break-word; word-break:normal; }
        .community-guide-card-details .guide-detail-label { color:#123B59; font-weight:800; }
        .community-guide-card-details .guide-detail-value { color:#47657A; font-weight:500; }
        .community-guide-card-footer { display:flex; align-items:center; justify-content:space-between; gap:.5rem; margin-top:auto; padding-top:.75rem; color:var(--tone); font-size:.7rem; font-weight:800; }
        .community-guide-arrow { display:grid; place-items:center; width:25px; height:25px; border-radius:50%; background:color-mix(in srgb,var(--tone) 10%,white); transition:transform .2s ease; }
        .community-guide-card:hover .community-guide-arrow { transform:translateX(3px); }
        .community-guide-back { display:inline-flex; margin-top:1rem; color:#1F77BE; font-size:.8rem; font-weight:750; text-decoration:none; }
        @keyframes guide-card-in { from { opacity:0; transform:translateY(9px); } to { opacity:1; transform:translateY(0); } }
        @media(max-width:680px){ .community-guide-hero{grid-template-columns:1fr;padding:1.35rem;min-height:0;} .community-guide-hero-stats{grid-template-columns:repeat(2,minmax(0,1fr));} .community-guide-grid{grid-template-columns:1fr;} .community-guide-card{min-height:145px;} .community-guide-section-head{align-items:flex-start;flex-direction:column;gap:.25rem;} }
        @media(prefers-reduced-motion:reduce){ .community-guide-card{animation:none;transition:none;} .community-guide-card:hover{transform:none;} }
    </style>

    <section class="community-guide-hero">
        <div>
            <div class="community-guide-kicker"><x-icon name="book" size="17" color="#DDF1FC" /> {{ brand()->name }}</div>
            <h1>Hướng dẫn sử dụng</h1>
            <p>Chọn một khu vực để mở ngay trang tương ứng. Mỗi mục được giải thích ngắn gọn để bạn biết nên làm gì tiếp theo.</p>
        </div>
        <div class="community-guide-hero-stats" aria-label="Tổng quan hướng dẫn">
            <div class="community-guide-stat"><strong>{{ count($sections) }}</strong><span>khu vực trong community</span></div>
            <div class="community-guide-stat"><strong>01</strong><span>nơi bắt đầu: Bảng tin</span></div>
        </div>
    </section>

    <div class="community-guide-section-head">
        <div><h2>Khám phá community</h2><p>Bấm vào thẻ để đi thẳng đến chức năng.</p></div>
    </div>

    <div class="community-guide-grid">
        @foreach($sections as $index => $section)
            @php
                $link = $section['link'];
                $tag = $link ? 'a' : 'article';
                $tone = $link['tone'] ?? 'blue';
                $toneColor = ['blue' => '#1F77BE', 'orange' => '#D97706', 'amber' => '#B7791F', 'teal' => '#147F96'][$tone] ?? '#1F77BE';
            @endphp
            @if($link)<a href="{{ $link['url'] }}" class="community-guide-card" style="--index:{{ $index }};--tone:{{ $toneColor }}">@else<article class="community-guide-card" style="--index:{{ $index }};--tone:#61798A">@endif
                <div class="community-guide-card-top">
                    <span class="community-guide-icon"><x-icon name="{{ $link['icon'] ?? 'book' }}" size="19" color="currentColor" /></span>
                    <span class="community-guide-number">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                </div>
                <h3>{{ $section['title'] }}</h3>
                <div class="community-guide-card-details">
                    @if(count($section['details']) > 0)
                        @foreach($section['details'] as $detail)
                            <p><span class="guide-detail-label">{{ $detail['label'] }}</span>@if($detail['value'] !== ''): <span class="guide-detail-value">{{ $detail['value'] }}</span>@endif</p>
                        @endforeach
                    @elseif($section['summary'] !== '')
                        <p>{{ $section['summary'] }}</p>
                    @else
                        <p>Thông tin hướng dẫn đang được cập nhật.</p>
                    @endif
                </div>
                @if($link)<div class="community-guide-card-footer"><span>Mở trang này</span><span class="community-guide-arrow" aria-hidden="true">→</span></div>@endif
            @if($link)</a>@else</article>@endif
        @endforeach
    </div>

    <a href="{{ community_route('feed') }}" class="community-guide-back">← Về bảng tin</a>
</div>
