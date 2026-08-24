<div class="community-home">
    <style>
        .community-home { width: min(100%, 910px); margin: 0 auto; padding: 24px clamp(16px, 3vw, 30px) 64px; color: var(--text); }
        .community-home-cover { position: relative; overflow: hidden; aspect-ratio: 16 / 9; border: 1px solid #BBD4DF; border-radius: 16px; background: #EAF4F7; box-shadow: 0 8px 20px rgba(18,59,89,.08); }
        .community-home-cover img { display: block; width: 100%; height: 100%; object-fit: cover; }
        .community-home-cover-fallback { position: absolute; inset: 0; display: grid; place-items: center; overflow: hidden; background: radial-gradient(circle at 75% 20%, rgba(141,226,255,.5), transparent 30%), linear-gradient(135deg, #0D4E78, #1F77BE 53%, #0A426B); }
        .community-home-cover-fallback::before { content: ''; position: absolute; width: 48%; aspect-ratio: 1; border: 1px solid rgba(255,255,255,.35); border-radius: 50%; box-shadow: 0 0 0 34px rgba(255,255,255,.07), 0 0 0 68px rgba(255,255,255,.04); }
        .community-home-cover-fallback img { position: relative; z-index: 1; width: min(23%, 132px); height: auto; max-height: 42%; object-fit: contain; padding: 11px; border-radius: 20px; background: rgba(255,255,255,.96); box-shadow: 0 12px 24px rgba(0,0,0,.2); }
        .community-home-cover-fallback span { position: relative; z-index: 1; color: #fff; font-size: clamp(34px, 6vw, 64px); font-weight: 850; letter-spacing: -.06em; }
        .community-home-actions { display: flex; align-items: center; justify-content: flex-end; gap: 9px; min-height: 46px; margin-top: 10px; }
        .community-home-actions a, .community-home-actions button { display: inline-flex; align-items: center; justify-content: center; min-height: 38px; padding: 8px 13px; border: 1px solid #B7D2DF; border-radius: 10px; background: #fff; color: #125A96; font: inherit; font-size: 12px; font-weight: 800; text-decoration: none; cursor: pointer; }
        .community-home-actions .community-home-primary { border-color: #1F77BE; background: #1F77BE; color: #fff; box-shadow: 0 5px 12px rgba(31,119,190,.18); }
        .community-home-actions a:hover, .community-home-actions button:hover { border-color: #1F77BE; background: #EEF8FB; }
        .community-home-actions .community-home-primary:hover { background: #125A96; color: #fff; }
        .community-home-stats { display: flex; flex-wrap: wrap; align-items: center; gap: 22px; padding: 5px 1px 1px; }
        .community-home-stat { display: inline-flex; align-items: center; gap: 7px; color: var(--text); font-size: 14px; font-weight: 700; white-space: nowrap; }
        .community-home-stat svg { width: 19px; height: 19px; flex: 0 0 auto; color: #125A96; }
        .community-home-stat strong { font-size: 16px; font-weight: 850; letter-spacing: -.02em; }
        .community-home-owner { display: flex; align-items: center; gap: 10px; margin-top: 15px; }
        .community-home-owner img, .community-home-owner-fallback { width: 38px; height: 38px; flex: 0 0 auto; border: 2px solid #fff; border-radius: 50%; background: #E1F4F7; box-shadow: 0 2px 7px rgba(18,59,89,.13); object-fit: cover; }
        .community-home-owner-fallback { display: grid; place-items: center; color: #125A96; font-size: 12px; font-weight: 850; }
        .community-home-owner-kicker { display: block; color: var(--text-muted); font-size: 11px; line-height: 1.2; }
        .community-home-owner-name { display: block; margin-top: 2px; color: var(--text); font-size: 14px; font-weight: 800; }
        .community-home-section { margin-top: 28px; }
        .community-home-section-heading { display: flex; align-items: end; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
        .community-home-section-title { display: flex; align-items: center; gap: 8px; min-width: 0; }
        .community-home-section-title svg { width: 19px; height: 19px; flex: 0 0 auto; color: #1F77BE; }
        .community-home-section-title h2 { margin: 0; color: var(--text); font-size: 19px; font-weight: 800; letter-spacing: -.03em; }
        .community-home-section-title b { display: inline-grid; min-width: 20px; height: 20px; place-items: center; border-radius: 999px; background: #E1F4F7; color: #125A96; font-size: 10px; }
        .community-home-section-heading p { margin: 4px 0 0; color: var(--text-muted); font-size: 12px; line-height: 1.5; }
        .community-home-section-link { flex: 0 0 auto; color: #1580B5; font-size: 12px; font-weight: 800; text-decoration: none; white-space: nowrap; }
        .community-home-section-link:hover { color: #125A96; text-decoration: underline; }
        .community-home-goal { padding: 22px 24px; border: 1px solid #E2EAEE; border-radius: 16px; background: #fff; box-shadow: 0 3px 10px rgba(18,59,89,.03); }
        .community-home-goal-title { margin: 0 0 7px; color: var(--text); font-size: 15px; font-weight: 800; line-height: 1.5; }
        .community-home-goal-copy { margin: 0; color: var(--text-secondary); font-size: 14px; line-height: 1.7; }
        .community-home-rail { display: grid; grid-auto-flow: column; grid-auto-columns: minmax(300px, 42%); gap: 14px; overflow-x: auto; padding: 2px 2px 12px; scroll-snap-type: x proximity; scrollbar-width: thin; }
        .community-home-card { display: flex; min-width: 0; flex-direction: column; overflow: hidden; border: 1px solid #DCE7EC; border-radius: 15px; background: #fff; color: inherit; text-decoration: none; scroll-snap-align: start; box-shadow: 0 3px 10px rgba(18,59,89,.04); transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease; }
        .community-home-card:hover { border-color: #8ABACE; box-shadow: 0 10px 22px rgba(18,59,89,.11); transform: translateY(-2px); }
        .community-home-card:focus-visible { outline: 3px solid rgba(31,119,190,.32); outline-offset: 2px; }
        .community-home-card-cover { display: grid; min-height: 150px; place-items: center; overflow: hidden; background: #E9F5F8; }
        .community-home-card-cover img { width: 100%; height: 100%; object-fit: cover; }
        .community-home-card-cover.challenge { background: radial-gradient(circle at 76% 20%, rgba(141,226,255,.34), transparent 32%), linear-gradient(135deg, #0B4D76, #1678B6); color: #fff; }
        .community-home-card-cover.challenge strong { display: block; text-align: center; font-size: 31px; font-weight: 850; letter-spacing: -.05em; }
        .community-home-card-cover.challenge span { display: block; margin-top: 4px; color: rgba(255,255,255,.8); font-size: 10px; font-weight: 800; letter-spacing: .12em; text-align: center; text-transform: uppercase; }
        .community-home-card-cover.course-fallback { color: #125A96; }
        .community-home-card-cover.course-fallback svg { width: 38px; height: 38px; }
        .community-home-card-body { display: flex; flex: 1; flex-direction: column; padding: 15px 16px 16px; }
        .community-home-card-body h3 { margin: 0; color: var(--text); font-size: 16px; line-height: 1.4; }
        .community-home-card-body p { display: -webkit-box; min-height: 39px; margin: 7px 0 13px; overflow: hidden; color: var(--text-secondary); font-size: 13px; line-height: 1.55; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
        .community-home-card-meta { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: auto; color: var(--text-muted); font-size: 11px; }
        .community-home-card-meta b { color: #1580B5; font-size: 12px; }
        .community-home-empty { padding: 21px 22px; border: 1px dashed #BCD2DC; border-radius: 14px; background: #FBFDFE; color: var(--text-muted); font-size: 13px; line-height: 1.6; }
        @media (max-width: 700px) {
            .community-home { padding: 16px 14px 46px; }
            .community-home-cover { border-radius: 14px; }
            .community-home-actions { justify-content: stretch; }
            .community-home-actions a, .community-home-actions button { flex: 1; }
            .community-home-stats { gap: 14px; }
            .community-home-stat { gap: 5px; font-size: 12px; }
            .community-home-stat svg { width: 17px; height: 17px; }
            .community-home-stat strong { font-size: 14px; }
            .community-home-section { margin-top: 24px; }
            .community-home-section-title h2 { font-size: 17px; }
            .community-home-goal { padding: 18px; }
            .community-home-rail { grid-auto-columns: minmax(265px, 88%); }
        }
    </style>

    <section class="community-home-cover" aria-label="Ảnh bìa cộng đồng">
        @if($community->banner_path)
            <img src="{{ asset('storage/'.$community->banner_path) }}" alt="Ảnh bìa {{ $community->name }}">
        @else
            <div class="community-home-cover-fallback" aria-hidden="true">
                @if(($community->slug ?? null) === 'dscons')
                    <img src="{{ asset('1024x1024-da xoa nen.png') }}" alt="">
                @elseif($community->logo_path)
                    <img src="{{ asset('storage/'.$community->logo_path) }}" alt="">
                @else
                    <span>{{ strtoupper(substr($community->name, 0, 1)) }}</span>
                @endif
            </div>
        @endif
    </section>

    <div class="community-home-actions">
        @if($canManage)
            <a href="{{ route('community.manage', ['community' => $community->slug]) }}">Quản lý community</a>
        @endif
        @auth
            @if($isMember)
                <a href="{{ route('community.feed', ['community' => $community->slug]) }}" class="community-home-primary">Vào bảng tin →</a>
            @else
                <button wire:click="join" class="community-home-primary" wire:loading.attr="disabled" wire:target="join">Tham gia Free →</button>
            @endif
        @else
            <a href="{{ route('login') }}" class="community-home-primary">Đăng nhập để tham gia →</a>
        @endauth
    </div>

    <div class="community-home-stats" aria-label="Quy mô cộng đồng">
        <div class="community-home-stat"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg><span><strong>{{ number_format($memberCount) }}</strong> thành viên</span></div>
        <div class="community-home-stat"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg><span><strong>{{ number_format($courseCount) }}</strong> khóa học</span></div>
        <div class="community-home-stat"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m13 2-9 12h7l-1 8 9-12h-7l1-8Z"/></svg><span><strong>{{ number_format($challengeCount) }}</strong> challenge</span></div>
    </div>

    <div class="community-home-owner">
        @if($creator)<img src="{{ $creator->avatar_url }}" alt="">@else<div class="community-home-owner-fallback">DS</div>@endif
        <div><span class="community-home-owner-kicker">Người tạo cộng đồng</span><strong class="community-home-owner-name">{{ $creator?->name ?: 'Đội ngũ DSCons' }}</strong></div>
    </div>

    <section class="community-home-section">
        <div class="community-home-section-heading">
            <div class="community-home-section-title"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v18M3 12h18"/></svg><h2>Mục tiêu của cộng đồng</h2></div>
            <span style="color:var(--text-muted);font-size:11px;">{{ $eventCount }} sự kiện</span>
        </div>
        <div class="community-home-goal">
            <p class="community-home-goal-title">{{ $community->tagline ?: 'Học tập, thực hành và phát triển cùng nhau.' }}</p>
            <p class="community-home-goal-copy">{{ $community->description ?: 'Khám phá nội dung thực chiến, những challenge mới và kết nối với các thành viên có cùng mục tiêu.' }}</p>
        </div>
    </section>

    <section class="community-home-section">
        <div class="community-home-section-heading">
            <div><div class="community-home-section-title"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m13 2-9 12h7l-1 8 9-12h-7l1-8Z"/></svg><h2>Challenge đang mở</h2><b>{{ $challengeCount }}</b></div><p>Chọn một mục tiêu và bắt đầu hành động.</p></div>
            <a href="{{ route('community.challenge', ['community' => $community->slug]) }}" class="community-home-section-link">Xem tất cả →</a>
        </div>
        @if($challenges->isEmpty())
            <div class="community-home-empty">Cộng đồng chưa có Challenge đang mở.</div>
        @else
            <div class="community-home-rail" aria-label="Danh sách Challenge đang mở">
                @foreach($challenges as $challenge)
                    <a class="community-home-card" href="{{ route('community.challenge.show', ['community' => $community->slug, 'slug' => $challenge->slug]) }}">
                        @if($challenge->cover_path)
                            <div class="community-home-card-cover"><img src="{{ asset('storage/'.$challenge->cover_path) }}" alt=""></div>
                        @else
                            <div class="community-home-card-cover challenge"><div><strong>{{ $challenge->required_days ?: '∞' }}</strong><span>ngày thực chiến</span></div></div>
                        @endif
                        <div class="community-home-card-body"><h3>{{ $challenge->title }}</h3><p>{{ $challenge->description ?: 'Bắt đầu một lộ trình thực hành mới cùng cộng đồng.' }}</p><div class="community-home-card-meta"><span>{{ $challenge->activeMembersCount() }} tham gia · {{ ucfirst($challenge->difficulty ?: 'Thực chiến') }}</span><b>Xem challenge →</b></div></div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section class="community-home-section">
        <div class="community-home-section-heading">
            <div><div class="community-home-section-title"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg><h2>Khóa học giúp bạn tăng tốc</h2><b>{{ $courseCount }}</b></div><p>Kiến thức và công cụ để tiến xa hơn trong challenge.</p></div>
            <a href="{{ route('community.academy', ['community' => $community->slug]) }}" class="community-home-section-link">Xem tất cả →</a>
        </div>
        @if($courses->isEmpty())
            <div class="community-home-empty">Cộng đồng chưa có khóa học được xuất bản.</div>
        @else
            <div class="community-home-rail" aria-label="Danh sách khóa học nổi bật">
                @foreach($courses as $course)
                    <a class="community-home-card" href="{{ route('community.academy.show', ['community' => $community->slug, 'id' => $course->id]) }}">
                        @if($course->thumbnail)
                            <div class="community-home-card-cover"><img src="{{ asset('storage/'.$course->thumbnail) }}" alt=""></div>
                        @else
                            <div class="community-home-card-cover course-fallback"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg></div>
                        @endif
                        <div class="community-home-card-body"><h3>{{ $course->title }}</h3><p>{{ $course->description ?: 'Nội dung học tập thực chiến dành cho thành viên.' }}</p><div class="community-home-card-meta"><span>{{ $course->modules_count }} bài học · {{ ucfirst($course->difficulty ?: 'Thực chiến') }}</span><b>Xem khóa học →</b></div></div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</div>
