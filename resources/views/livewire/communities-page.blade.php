<style>
    .discovery-page { max-width: 1100px; margin: 0 auto; padding: 34px 30px 64px; color: var(--text); }
    .discovery-heading { display: flex; align-items: end; justify-content: space-between; gap: 24px; padding-bottom: 24px; border-bottom: 1px solid var(--border); }
    .discovery-kicker { display: flex; align-items: center; gap: 7px; margin-bottom: 8px; color: var(--green); font-size: 11px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
    .discovery-kicker svg { width: 15px; height: 15px; }
    .discovery-heading h1 { margin: 0; color: var(--text); font-size: clamp(28px, 3vw, 34px); font-weight: 800; letter-spacing: -.045em; line-height: 1.1; }
    .discovery-heading p { max-width: 620px; margin: 9px 0 0; color: #627D90; font-size: 14px; line-height: 1.65; }
    .discovery-create { min-height: 42px; padding: 10px 17px; border-radius: 13px !important; white-space: nowrap; text-decoration: none; }
    .discovery-search-row { display: flex; align-items: center; gap: 13px; margin: 22px 0 26px; }
    .discovery-search { position: relative; flex: 1; }
    .discovery-search svg { position: absolute; top: 50%; left: 15px; width: 18px; height: 18px; color: var(--text-muted); transform: translateY(-50%); pointer-events: none; }
    .discovery-search input { width: 100%; min-height: 48px; box-sizing: border-box; padding: 12px 16px 12px 44px; border: 1px solid var(--border); border-radius: 14px; background: #fff; color: var(--text); font: inherit; outline: none; }
    .discovery-search input:focus { border-color: var(--green); box-shadow: 0 0 0 3px rgba(31,119,190,.13); }
    .discovery-count { color: var(--text-muted); font-size: 12px; white-space: nowrap; }
    .discovery-section { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 14px; }
    .discovery-section h2 { margin: 0; color: var(--text); font-size: 18px; letter-spacing: -.02em; }
    .discovery-section span { color: var(--text-muted); font-size: 12px; }
    .discovery-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
    .discovery-card { position: relative; display: flex; min-width: 0; flex-direction: column; overflow: hidden; border: 1px solid #D7E4EA; border-radius: 18px; background: #fff; box-shadow: 0 2px 8px rgba(18,59,89,.035); transition: transform .17s ease, border-color .17s ease, box-shadow .17s ease; }
    .discovery-card:hover { border-color: #A9CAD7; box-shadow: 0 10px 24px rgba(18,59,89,.09); transform: translateY(-2px); }
    .discovery-card-link { position: absolute; z-index: 1; inset: 0; border-radius: inherit; }
    .discovery-card-cover { position: relative; aspect-ratio: 16 / 7; overflow: hidden; background: linear-gradient(135deg, #ECF8F9 0%, #C0E5E9 55%, #6EBCCD 100%); }
    .discovery-card-cover::after { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, rgba(255,255,255,.4), transparent 58%); }
    .discovery-card-cover-image { width: 100%; height: 100%; object-fit: cover; }
    .discovery-card-cover-mark { position: absolute; z-index: 1; inset: 0; display: grid; place-items: center; }
    .discovery-card-cover-mark img { width: 70px; height: 70px; object-fit: contain; filter: drop-shadow(0 7px 12px rgba(18,59,89,.16)); }
    .discovery-card-badge { position: absolute; z-index: 2; top: 10px; right: 10px; padding: 4px 8px; border-radius: 999px; background: rgba(255,255,255,.94); color: var(--green); font-size: 10px; font-weight: 800; }
    .discovery-card-info { position: relative; z-index: 2; display: flex; flex: 1; flex-direction: column; padding: 14px 15px 15px; pointer-events: none; }
    .discovery-card-heading { display: flex; align-items: center; gap: 9px; min-width: 0; }
    .discovery-card-logo { width: 33px; height: 33px; box-sizing: border-box; flex: 0 0 auto; overflow: hidden; border: 1px solid #D6E8ED; border-radius: 9px; background: #E9F6F8; object-fit: contain; padding: 3px; }
    .discovery-card-heading h3 { overflow: hidden; margin: 0; color: var(--text); font-size: 16px; letter-spacing: -.015em; line-height: 1.25; text-overflow: ellipsis; white-space: nowrap; }
    .discovery-card-verified { color: var(--green); font-size: 12px; }
    .discovery-card-slug { margin-top: 3px; color: var(--text-muted); font-size: 11px; }
    .discovery-card-description { display: -webkit-box; min-height: 39px; margin: 10px 0 12px; overflow: hidden; color: #5E788A; font-size: 12px; line-height: 1.6; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
    .discovery-card-meta { display: flex; gap: 8px; margin-top: auto; padding-top: 10px; border-top: 1px solid #E7EEF1; color: var(--text-muted); font-size: 11px; }
    .discovery-card-meta strong { color: var(--text); font-weight: 800; }
    .discovery-primary-action { display: inline-flex; align-items: center; justify-content: center; gap: 7px; border: 1px solid #1F77BE !important; border-radius: 12px !important; background: #1F77BE !important; color: #fff !important; font-weight: 750 !important; box-shadow: 0 5px 12px rgba(31,119,190,.22); }
    .discovery-primary-action:hover { border-color: #125A96 !important; background: #125A96 !important; box-shadow: 0 8px 16px rgba(18,90,150,.26); transform: translateY(-1px); }
    .discovery-primary-action:active { transform: translateY(0); box-shadow: 0 2px 6px rgba(18,90,150,.2); }
    .discovery-card-cta { position: relative; z-index: 3; width: 100%; min-height: 37px; margin-top: 13px; padding: 8px 10px; font-size: 12px; pointer-events: auto; }
    .discovery-create-card { display: flex; min-height: 270px; align-items: center; justify-content: center; padding: 24px; border: 1px dashed #AACCD7; border-radius: 18px; background: #F7FBFC; color: var(--green); text-align: center; text-decoration: none; transition: background .17s ease, border-color .17s ease; }
    .discovery-create-card:hover { border-color: var(--green); background: #EDF8FA; }
    .discovery-create-card svg { width: 28px; height: 28px; margin-bottom: 10px; }
    .discovery-create-card strong { display: block; font-size: 15px; }
    .discovery-create-card span { display: block; margin-top: 4px; color: var(--text-muted); font-size: 12px; line-height: 1.5; }
    .discovery-empty { padding: 48px 24px; border: 1px dashed #BFD5DE; border-radius: 14px; background: #fff; color: var(--text-muted); text-align: center; }
    @media (max-width: 1050px) { .discovery-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 700px) { .discovery-page { padding: 20px 14px 42px; } .discovery-heading { display: block; padding-bottom: 20px; } .discovery-create { display: flex; justify-content: center; margin-top: 16px; } .discovery-search-row { display: block; margin: 16px 0 22px; } .discovery-count { display: block; margin: 9px 0 0; } .discovery-grid { grid-template-columns: 1fr; gap: 13px; } }
</style>

<div class="discovery-page">
    <header class="discovery-heading">
        <div>
            <div class="discovery-kicker">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></svg>
                Khám phá
            </div>
            <h1>Khám phá cộng đồng</h1>
            <p>Tìm cộng đồng phù hợp với mục tiêu học tập, nghề nghiệp và những người bạn muốn đồng hành.</p>
        </div>
        @auth
            <a href="{{ route('community.create') }}" class="ds-btn discovery-create discovery-primary-action"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true" style="width:15px;height:15px;"><path d="M12 5v14M5 12h14"/></svg>Tạo cộng đồng</a>
        @endauth
    </header>

    <div class="discovery-search-row">
        <label class="discovery-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 5 5"/></svg>
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Tìm cộng đồng theo tên hoặc mô tả…" aria-label="Tìm cộng đồng">
        </label>
        <span class="discovery-count">{{ $communities->count() }} community đang hoạt động</span>
    </div>

    <div class="discovery-section">
        <h2>Danh sách cộng đồng</h2>
        <span>Bấm vào community để truy cập</span>
    </div>

    @if($communities->isEmpty())
        <div class="discovery-empty">Chưa có community phù hợp. Hãy thử một từ khóa khác.</div>
    @else
        <div class="discovery-grid">
            @foreach($communities as $community)
                @php
                    $isJoined = $joinedIds->contains($community->id);
                    $communityUrl = $isJoined
                        ? route('community.feed', ['community' => $community->slug])
                        : route('community.preview', $community->slug);
                @endphp
                <article class="discovery-card">
                    <a href="{{ $communityUrl }}" class="discovery-card-link" aria-label="{{ $isJoined ? 'Vào' : 'Xem' }} community {{ $community->name }}"></a>
                    <div class="discovery-card-cover">
                        @if($community->banner_path)
                            <img class="discovery-card-cover-image" src="{{ asset('storage/'.$community->banner_path) }}" alt="">
                        @else
                            <div class="discovery-card-cover-mark">
                                @if(($community->slug ?? null) === 'dscons')
                                    <img src="{{ asset('1024x1024-da xoa nen.png') }}" alt="">
                                @elseif($community->logo_path)
                                    <img src="{{ asset('storage/'.$community->logo_path) }}" alt="">
                                @else
                                    <span style="font-size:38px;font-weight:850;color:rgba(255,255,255,.92);">{{ strtoupper(substr($community->name, 0, 1)) }}</span>
                                @endif
                            </div>
                        @endif
                        @if($community->isVerified())<span class="discovery-card-badge">Đã xác minh</span>@endif
                    </div>
                    <div class="discovery-card-info">
                        <div class="discovery-card-heading">
                            @if(($community->slug ?? null) === 'dscons')
                                <img class="discovery-card-logo" src="{{ asset('1024x1024-da xoa nen.png') }}" alt="DSCons">
                            @elseif($community->logo_path)
                                <img class="discovery-card-logo" src="{{ asset('storage/'.$community->logo_path) }}" alt="">
                            @else
                                <span class="discovery-card-logo" style="display:grid;place-items:center;color:var(--green);font-weight:800;">{{ strtoupper(substr($community->name, 0, 1)) }}</span>
                            @endif
                            <div style="min-width:0;">
                                <h3>{{ $community->name }} @if($community->isVerified())<span class="discovery-card-verified" title="Đã xác minh">✓</span>@endif</h3>
                                <div class="discovery-card-slug">/c/{{ $community->slug }}</div>
                            </div>
                        </div>
                        <p class="discovery-card-description">{{ $community->description ?: ($community->tagline ?: 'Một cộng đồng học tập thực chiến trên DSCons.') }}</p>
                        <div class="discovery-card-meta"><span><strong>{{ number_format($community->users_count) }}</strong> thành viên</span><span aria-hidden="true">·</span><span>{{ $isJoined ? 'Đã tham gia' : 'Mở tham gia' }}</span></div>
                        <a href="{{ $communityUrl }}" class="ds-btn discovery-card-cta discovery-primary-action" style="text-align:center;text-decoration:none;">{{ $isJoined ? 'Vào community' : 'Xem & tham gia' }}<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width:14px;height:14px;"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
                    </div>
                </article>
            @endforeach
            @if($communities->count() < 3)
                <a href="{{ route('community.create') }}" class="discovery-create-card">
                    <div>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
                        <strong>Tạo community mới</strong>
                        <span>Gửi đề xuất để nền tảng duyệt và mở community của bạn.</span>
                    </div>
                </a>
            @endif
        </div>
    @endif
</div>
