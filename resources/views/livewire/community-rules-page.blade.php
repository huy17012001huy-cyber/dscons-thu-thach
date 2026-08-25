<div class="community-rules-page">
    <style>
        .community-rules-page { max-width:960px; margin:0 auto; }
        .rules-hero { position:relative; isolation:isolate; padding:1.8rem 2rem; border-radius:22px; background:linear-gradient(125deg,#103E62,#1F77BE); color:#fff; overflow:hidden; box-shadow:0 16px 34px rgba(31,119,190,.18); }
        .rules-hero::after { content:""; position:absolute; z-index:-1; right:-30px; bottom:-100px; width:250px; height:250px; border:1px solid rgba(255,255,255,.28); border-radius:50%; box-shadow:0 0 0 28px rgba(255,255,255,.06),0 0 0 56px rgba(255,255,255,.04); }
        .rules-kicker { display:flex; align-items:center; gap:.55rem; color:#DDF1FC; font-size:.7rem; font-weight:850; letter-spacing:.12em; text-transform:uppercase; }
        .rules-hero h1 { margin:.55rem 0 .45rem; color:#fff; font-size:clamp(1.65rem,3vw,2.25rem); letter-spacing:-.04em; }
        .rules-hero p { max-width:720px; margin:0; color:#E6F4FC; font-size:.88rem; line-height:1.7; }
        .rules-hero-meta { display:flex; flex-wrap:wrap; gap:.5rem; margin-top:1rem; }
        .rules-hero-meta span { display:inline-flex; align-items:center; min-height:29px; padding:.35rem .65rem; border:1px solid rgba(255,255,255,.25); border-radius:999px; background:rgba(255,255,255,.1); color:#fff; font-size:.7rem; font-weight:800; }
        .rules-intro { display:grid; grid-template-columns:42px minmax(0,1fr); gap:.8rem; margin:1rem 0; padding:1rem 1.1rem; border:1px solid #EACB91; border-radius:15px; background:#FFF9ED; box-shadow:0 4px 13px rgba(120,90,20,.05); }
        .rules-intro-icon { display:grid; place-items:center; width:40px; height:40px; border-radius:12px; background:#FFF0C9; color:#A16207; }
        .rules-intro h2 { margin:0 0 .35rem; color:#7A4A08; font-size:.95rem; font-weight:800; }
        .rules-intro p { margin:0; color:#765D31; font-size:.83rem; line-height:1.7; white-space:pre-line; }
        .rules-flow { position:relative; display:grid; gap:.85rem; }
        .rules-flow::before { content:""; position:absolute; left:25px; top:18px; bottom:18px; width:2px; background:#C9DFEA; }
        .rule-card { position:relative; display:grid; grid-template-columns:52px minmax(0,1fr); gap:.9rem; padding:1rem 1.1rem; border:1px solid #C9DFEA; border-radius:16px; background:#fff; box-shadow:0 4px 13px rgba(18,59,89,.045); animation:rule-card-in .45s both; animation-delay:calc(var(--index) * 45ms); transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease; }
        .rule-card:hover { transform:translateX(4px); border-color:#9FC6DA; box-shadow:0 10px 22px rgba(18,59,89,.09); }
        .rule-number { position:relative; z-index:1; display:grid; place-items:center; width:50px; height:50px; border:5px solid #F7FBFE; border-radius:15px; background:#EAF4FC; color:#1F77BE; font-size:.75rem; font-weight:850; box-shadow:0 0 0 1px #B9D8E8; }
        .rule-card h2 { margin:.2rem 0 .35rem; color:#123B59; font-size:.96rem; font-weight:800; line-height:1.3; }
        .rule-card p { margin:0; color:#47657A; font-size:.83rem; line-height:1.7; white-space:pre-line; }
        .rules-note { margin-top:1rem; padding:1rem 1.1rem; border:1px solid #B9D8E8; border-radius:15px; background:#F7FCFF; }
        .rules-note strong { display:block; color:#123B59; font-size:.88rem; }
        .rules-note p { margin:.3rem 0 0; color:#47657A; font-size:.8rem; line-height:1.7; }
        .rules-contact { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-top:1rem; padding:1rem 1.1rem; border:1px solid #B9D8E8; border-radius:15px; background:#F0F8FD; }
        .rules-contact strong { display:block; color:#123B59; font-size:.88rem; }
        .rules-contact span { display:block; margin-top:.2rem; color:#61798A; font-size:.76rem; }
        .rules-contact a { display:inline-flex; align-items:center; justify-content:center; min-height:40px; padding:.55rem .8rem; border-radius:9px; background:#1F77BE; color:#fff; font-size:.76rem; font-weight:800; text-decoration:none; white-space:nowrap; }
        .rules-back { display:inline-flex; margin-top:1rem; color:#1F77BE; font-size:.8rem; font-weight:750; text-decoration:none; }
        @keyframes rule-card-in { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
        @media(max-width:640px){ .rules-hero{padding:1.35rem;} .rule-card{grid-template-columns:40px minmax(0,1fr);gap:.7rem;padding:.9rem;} .rule-number{width:38px;height:38px;border-width:4px;border-radius:12px;} .rules-flow::before{left:19px;} .rules-contact{align-items:stretch;flex-direction:column;} .rules-contact a{width:100%;} }
        @media(prefers-reduced-motion:reduce){ .rule-card{animation:none;transition:none;} .rule-card:hover{transform:none;} }
    </style>

    <section class="rules-hero">
        <div class="rules-kicker"><x-icon name="shield" size="17" color="#DDF1FC" /> {{ brand()->name }}</div>
        <h1>Nội quy cộng đồng</h1>
        <p>Đây là những nguyên tắc chung để mọi người có thể học tập, chia sẻ và phát triển trong một không gian tử tế. Nội quy là kim chỉ nam, không phải những quy định cứng nhắc.</p>
        <div class="rules-hero-meta"><span>{{ max(count($sections) - 2, 0) }} nguyên tắc chính</span><span>Chào đón · Tôn trọng · Cùng phát triển</span></div>
    </section>

    @if(isset($sections[0]))
        <section class="rules-intro">
            <div class="rules-intro-icon"><x-icon name="heart" size="20" color="#A16207" /></div>
            <div><h2>{{ $sections[0]['title'] }}</h2><p>{{ $sections[0]['body'] }}</p></div>
        </section>
    @endif

    @php
        $rules = array_slice($sections, 1, -1);
        $closingSection = $sections[count($sections) - 1] ?? null;
    @endphp

    <div class="rules-flow">
        @foreach($rules as $index => $section)
            <article class="rule-card" style="--index:{{ $index }}">
                <div class="rule-number">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</div>
                @php($ruleTitle = preg_replace('/^\d+\.\s*/u', '', $section['title']))
                <div><h2>{{ $ruleTitle }}</h2><p>{{ $section['body'] }}</p></div>
            </article>
        @endforeach
    </div>

    @if($closingSection)
        <section class="rules-note">
            <strong>{{ $closingSection['title'] }}</strong>
            <p>{{ $closingSection['body'] }}</p>
        </section>
    @endif

    <div class="rules-contact">
        <div><strong>Gặp vấn đề hoặc cần báo cáo?</strong><span>Mô tả cụ thể để quản trị viên có thể hỗ trợ bạn nhanh và công bằng.</span></div>
        <a href="{{ community_route('feedbacks.create') }}"><x-icon name="chat" size="16" color="#fff" /> Gửi góp ý</a>
    </div>

    <a href="{{ community_route('feed') }}" class="rules-back">← Về Bảng tin</a>
</div>
