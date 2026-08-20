<style>
    .discovery-page { max-width: 1180px; margin: 0 auto; padding: 26px 30px 64px; color: var(--text); }
    .discovery-hero { position: relative; min-height: 188px; display: flex; align-items: stretch; overflow: hidden; border: 1px solid #CFE1EA; border-radius: 20px; background: #F1F8FA; box-shadow: 0 8px 24px rgba(18,59,89,.06); }
    .discovery-hero-copy { position: relative; z-index: 1; width: 62%; padding: 30px 34px; background: rgba(255,255,255,.86); }
    .discovery-eyebrow { display: flex; align-items: center; gap: 7px; color: var(--green); font-size: 11px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; }
    .discovery-eyebrow svg { width: 16px; height: 16px; }
    .discovery-hero h1 { margin: 11px 0 7px; color: var(--text); font-size: clamp(26px, 3vw, 38px); line-height: 1.08; letter-spacing: -.045em; }
    .discovery-hero p { max-width: 550px; margin: 0; color: #557084; font-size: 14px; line-height: 1.65; }
    .discovery-hero p a { color: var(--green); font-weight: 750; text-decoration: none; }
    .discovery-hero p a:hover { text-decoration: underline; }
    .discovery-hero-visual { position: absolute; inset: 0 0 0 auto; width: 42%; overflow: hidden; background: linear-gradient(135deg, #DDF3F6 0%, #A8DCE6 45%, #1F77BE 100%); }
    .discovery-hero-visual::before { content: ''; position: absolute; inset: 0; opacity: .38; background-image: linear-gradient(rgba(255,255,255,.65) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.65) 1px, transparent 1px); background-size: 28px 28px; transform: skewX(-18deg) scale(1.2); }
    .discovery-hero-visual::after { content: ''; position: absolute; width: 280px; height: 280px; right: -70px; top: -70px; border: 1px solid rgba(255,255,255,.5); border-radius: 50%; box-shadow: 0 0 0 24px rgba(255,255,255,.12), 0 0 0 48px rgba(255,255,255,.08); }
    .discovery-hero-mark { position: absolute; z-index: 1; right: 16%; top: 50%; width: 112px; height: 112px; object-fit: contain; transform: translateY(-50%); filter: drop-shadow(0 12px 18px rgba(7,50,79,.24)); }
    .discovery-toolbar { display: flex; align-items: center; gap: 14px; margin: 22px 0 25px; }
    .discovery-search { position: relative; flex: 1; }
    .discovery-search svg { position: absolute; left: 15px; top: 50%; width: 18px; height: 18px; color: var(--text-muted); transform: translateY(-50%); pointer-events: none; }
    .discovery-search input { width: 100%; min-height: 48px; box-sizing: border-box; padding: 12px 16px 12px 44px; border: 1px solid var(--border); border-radius: 12px; background: #fff; color: var(--text); font: inherit; outline: none; box-shadow: 0 2px 8px rgba(18,59,89,.03); }
    .discovery-search input:focus { border-color: var(--green); box-shadow: 0 0 0 3px rgba(31,119,190,.13); }
    .discovery-create { min-height: 46px; padding: 11px 16px; border-radius: 10px; white-space: nowrap; text-decoration: none; }
    .discovery-section-head { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; margin-bottom: 13px; }
    .discovery-section-head h2 { margin: 0; font-size: 18px; letter-spacing: -.02em; }
    .discovery-section-head span { color: var(--text-muted); font-size: 13px; }
    .discovery-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
    .discovery-card { min-width: 0; display: flex; flex-direction: column; overflow: hidden; border: 1px solid #D5E3E9; border-radius: 16px; background: #fff; box-shadow: 0 4px 14px rgba(18,59,89,.045); transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease; }
    .discovery-card:hover { border-color: #AFCFDA; box-shadow: 0 12px 26px rgba(18,59,89,.1); transform: translateY(-2px); }
    .discovery-cover { position: relative; aspect-ratio: 16/8; overflow: hidden; background: linear-gradient(135deg, #E5F5F7, #B6DCE7); }
    .discovery-cover::after { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, transparent 45%, rgba(7,39,63,.22)); pointer-events: none; }
    .discovery-cover img.discovery-cover-image { width: 100%; height: 100%; object-fit: cover; }
    .discovery-cover-mark { position: absolute; inset: 0; z-index: 1; display: grid; place-items: center; }
    .discovery-cover-mark img { width: 82px; height: 82px; object-fit: contain; filter: drop-shadow(0 8px 13px rgba(18,59,89,.2)); }
    .discovery-badge { position: absolute; z-index: 2; top: 11px; right: 11px; padding: 5px 9px; border-radius: 999px; background: #fff; color: var(--green); font-size: 10px; font-weight: 800; box-shadow: 0 4px 10px rgba(18,59,89,.12); }
    .discovery-card-body { display: flex; flex: 1; flex-direction: column; padding: 15px 16px 16px; }
    .discovery-card-heading { display: flex; align-items: center; gap: 9px; min-width: 0; }
    .discovery-card-logo { width: 34px; height: 34px; flex: 0 0 auto; overflow: hidden; border: 1px solid #D5E8EE; border-radius: 10px; background: #E8F5F7; object-fit: contain; padding: 3px; }
    .discovery-card-heading h3 { overflow: hidden; margin: 0; color: var(--text); font-size: 16px; line-height: 1.25; text-overflow: ellipsis; white-space: nowrap; }
    .discovery-verified { color: var(--green); font-size: 12px; }
    .discovery-slug { margin-top: 4px; color: var(--text-muted); font-size: 11px; }
    .discovery-description { display: -webkit-box; min-height: 42px; margin: 11px 0 13px; overflow: hidden; color: #557084; font-size: 13px; line-height: 1.55; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
    .discovery-meta { display: flex; align-items: center; gap: 9px; margin-top: auto; padding-top: 11px; border-top: 1px solid #E5EEF2; color: var(--text-muted); font-size: 12px; }
    .discovery-meta strong { color: var(--text); font-weight: 750; }
    .discovery-actions { display: flex; gap: 8px; margin-top: 14px; }
    .discovery-actions .ds-btn { min-height: 38px; padding: 8px 10px; font-size: 12px; }
    .discovery-empty { padding: 48px 24px; border: 1px dashed #BFD5DE; border-radius: 16px; background: #fff; color: var(--text-muted); text-align: center; }
    @media (max-width: 1050px) { .discovery-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 760px) { .discovery-page { padding: 16px 14px 40px; } .discovery-hero { min-height: 244px; } .discovery-hero-copy { width: 100%; padding: 24px 22px; background: linear-gradient(90deg, rgba(255,255,255,.97) 0%, rgba(255,255,255,.89) 68%, rgba(255,255,255,.4) 100%); } .discovery-hero-visual { width: 58%; opacity: .8; } .discovery-hero-mark { right: 8%; top: 66%; width: 82px; height: 82px; } .discovery-toolbar { display: block; margin: 16px 0 22px; } .discovery-create { display: flex; justify-content: center; margin-top: 10px; } .discovery-grid { grid-template-columns: 1fr; gap: 13px; } }
</style>

<div class="discovery-page">
    <section class="discovery-hero" aria-labelledby="discovery-title">
        <div class="discovery-hero-copy">
            <div class="discovery-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></svg>
                DSCons Network
            </div>
            <h1 id="discovery-title">Khám phá cộng đồng</h1>
            <p>Tìm không gian học tập phù hợp với mục tiêu nghề nghiệp, kết nối cùng những người đang xây dựng năng lực thực chiến. <a href="{{ route('community.create') }}">Tạo cộng đồng của bạn</a>.</p>
        </div>
        <div class="discovery-hero-visual" aria-hidden="true">
            <img class="discovery-hero-mark" src="{{ asset('1024x1024-da xoa nen.png') }}" alt="">
        </div>
    </section>

    <div class="discovery-toolbar">
        <label class="discovery-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 5 5"/></svg>
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Tìm cộng đồng theo tên hoặc mô tả…" aria-label="Tìm cộng đồng">
        </label>
        @auth
            <a href="{{ route('community.create') }}" class="ds-btn ds-btn-primary discovery-create">Tạo cộng đồng</a>
        @endauth
    </div>

    <div class="discovery-section-head">
        <h2>Cộng đồng</h2>
        <span>{{ $communities->count() }} cộng đồng đang hoạt động</span>
    </div>

    @if($communities->isEmpty())
        <div class="discovery-empty">Chưa có cộng đồng phù hợp. Hãy thử từ khóa khác hoặc tạo cộng đồng đầu tiên.</div>
    @else
        <div class="discovery-grid">
            @foreach($communities as $community)
                <article class="discovery-card">
                    <div class="discovery-cover">
                        @if($community->banner_path)
                            <img class="discovery-cover-image" src="{{ asset('storage/'.$community->banner_path) }}" alt="">
                        @else
                            <div class="discovery-cover-mark">
                                @if(($community->slug ?? null) === 'dscons')
                                    <img src="{{ asset('1024x1024-da xoa nen.png') }}" alt="">
                                @elseif($community->logo_path)
                                    <img src="{{ asset('storage/'.$community->logo_path) }}" alt="">
                                @else
                                    <span style="font-size:44px;font-weight:850;color:rgba(255,255,255,.9);">{{ strtoupper(substr($community->name, 0, 1)) }}</span>
                                @endif
                            </div>
                        @endif
                        @if($community->isVerified())<span class="discovery-badge">Đã xác minh</span>@endif
                    </div>
                    <div class="discovery-card-body">
                        <div class="discovery-card-heading">
                            @if(($community->slug ?? null) === 'dscons')
                                <img class="discovery-card-logo" src="{{ asset('1024x1024-da xoa nen.png') }}" alt="DSCons">
                            @elseif($community->logo_path)
                                <img class="discovery-card-logo" src="{{ asset('storage/'.$community->logo_path) }}" alt="">
                            @else
                                <span class="discovery-card-logo" style="display:grid;place-items:center;color:var(--green);font-weight:800;">{{ strtoupper(substr($community->name, 0, 1)) }}</span>
                            @endif
                            <div style="min-width:0;">
                                <h3>{{ $community->name }} @if($community->isVerified())<span class="discovery-verified" title="Đã xác minh">✓</span>@endif</h3>
                                <div class="discovery-slug">/c/{{ $community->slug }}</div>
                            </div>
                        </div>
                        <p class="discovery-description">{{ $community->description ?: ($community->tagline ?: 'Một cộng đồng học tập thực chiến trên DSCons.') }}</p>
                        <div class="discovery-meta"><span><strong>{{ number_format($community->users_count) }}</strong> thành viên</span><span aria-hidden="true">·</span><span>{{ $community->isVerified() ? 'Đã xác minh' : 'Đang phát triển' }}</span></div>
                        <div class="discovery-actions">
                            <a href="{{ route('community.preview', $community->slug) }}" class="ds-btn" style="flex:1;text-align:center;text-decoration:none;">Xem community</a>
                            @auth
                                @if($joinedIds->contains($community->id))
                                    <a href="{{ route('community.feed', $community->slug) }}" class="ds-btn ds-btn-primary" style="flex:1;text-align:center;text-decoration:none;">Mở bảng tin</a>
                                @else
                                    <button wire:click="join({{ $community->id }})" class="ds-btn ds-btn-primary" style="flex:1;">Tham gia Free</button>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="ds-btn ds-btn-primary" style="flex:1;text-align:center;text-decoration:none;">Đăng nhập</a>
                            @endauth
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
