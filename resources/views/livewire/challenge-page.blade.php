<div class="challenge-page ds-learning-page">
    <style>
        .challenge-page { --challenge-blue:#1F77BE; --challenge-strong:#125A96; --challenge-ink:#123B59; --challenge-muted:#61798A; --challenge-surface:#FFFFFF; --challenge-soft:#F1F8FC; }
        .challenge-page .page-heading { display:flex; align-items:flex-end; justify-content:space-between; gap:18px; margin-bottom:18px; }
        .challenge-page .page-heading h1 { display:flex; align-items:center; gap:8px; margin:0; color:var(--challenge-ink); font-size:clamp(1.45rem,2vw,1.85rem); line-height:1.15; letter-spacing:-.035em; }
        .challenge-page .page-heading p { margin:.45rem 0 0; color:var(--challenge-muted); font-size:.86rem; line-height:1.55; }
        .challenge-tabs { display:flex; gap:5px; padding:5px; margin-bottom:20px; overflow-x:auto; border:1px solid #C9DFEA; border-radius:14px; background:var(--challenge-surface); scrollbar-width:none; }
        .challenge-tabs::-webkit-scrollbar { display:none; }
        .challenge-tab { min-height:42px; flex:0 0 auto; display:inline-flex; align-items:center; gap:7px; padding:0 14px; border:1px solid transparent; border-radius:10px; color:#61798A; background:transparent; font-size:.8rem; font-weight:750; cursor:pointer; transition:background .16s ease,color .16s ease,border-color .16s ease,transform .16s ease; }
        .challenge-tab:hover { color:var(--challenge-strong); background:#F1F8FC; }
        .challenge-tab.is-active { color:var(--challenge-strong); border-color:#A9D0E4; background:#E4F2F9; box-shadow:0 3px 8px rgba(18,90,150,.08); }
        .challenge-tab-count { display:grid; place-items:center; min-width:22px; height:22px; padding:0 6px; border-radius:999px; background:#fff; color:#125A96; font-size:.68rem; }
        .challenge-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:18px; }
        .challenge-card { display:flex; min-width:0; flex-direction:column; overflow:hidden; border:1px solid #C9DFEA; border-radius:16px; background:var(--challenge-surface); box-shadow:0 5px 16px rgba(18,59,89,.055); transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease; }
        .challenge-card:hover { transform:translateY(-3px); border-color:#8FB9CB; box-shadow:0 14px 28px rgba(18,59,89,.12); }
        .challenge-cover { position:relative; aspect-ratio:16/8.5; overflow:hidden; background:#DCECF7; }
        .challenge-cover img { width:100%; height:100%; object-fit:cover; transition:transform .35s ease; }
        .challenge-card:hover .challenge-cover img { transform:scale(1.035); }
        .challenge-cover-fallback { display:grid; place-items:center; align-content:center; gap:7px; width:100%; height:100%; color:#125A96; text-align:center; }
        .challenge-cover-fallback::before { position:absolute; inset:14px; border:1px solid rgba(31,119,190,.17); border-radius:12px; content:""; }
        .challenge-cover-fallback .cover-mark { position:relative; display:grid; place-items:center; width:42px; height:42px; border:1px solid rgba(31,119,190,.22); border-radius:12px; background:rgba(255,255,255,.62); }
        .challenge-cover-fallback strong, .challenge-cover-fallback small { position:relative; }
        .challenge-cover-fallback strong { font-size:.88rem; letter-spacing:.08em; text-transform:uppercase; }
        .challenge-cover-fallback small { color:#456477; font-size:.64rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
        .challenge-cover-badge { position:absolute; left:12px; top:12px; padding:5px 8px; border:1px solid rgba(255,255,255,.62); border-radius:999px; color:#fff; background:rgba(18,59,89,.9); font-size:.65rem; font-weight:800; }
        .challenge-card-body { display:flex; flex:1; flex-direction:column; padding:16px; }
        .challenge-card h2 { margin:0; color:var(--challenge-ink); font-size:1.04rem; line-height:1.32; letter-spacing:-.02em; }
        .challenge-description { min-height:42px; margin:7px 0 12px; color:var(--challenge-muted); font-size:.78rem; line-height:1.55; }
        .challenge-meta { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:14px; }
        .challenge-pill { display:inline-flex; align-items:center; gap:5px; min-height:26px; padding:0 8px; border:1px solid #D7E5EE; border-radius:999px; color:#456477; background:#F7FAFC; font-size:.67rem; font-weight:700; }
        .challenge-pill.is-normal { color:#125A96; background:#EAF5FB; border-color:#B8D7E6; }
        .challenge-pill.is-hard { color:#9A5B00; background:#FFF5DF; border-color:#F3D596; }
        .challenge-pill.is-chaos { color:#9B3D31; background:#FFF0EE; border-color:#F0C0B8; }
        .challenge-progress { margin:0 0 13px; }
        .challenge-progress-head { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:6px; color:var(--challenge-muted); font-size:.7rem; }
        .challenge-progress-head span { display:inline-flex; align-items:center; gap:5px; }
        .challenge-progress-head strong { color:#125A96; font-size:.72rem; }
        .challenge-progress-track { height:7px; overflow:hidden; border-radius:999px; background:#E7F0F4; }
        .challenge-progress-fill { height:100%; border-radius:inherit; background:#1F77BE; transition:width .25s ease; }
        .challenge-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:0; margin:0 -16px; padding:10px 4px; border-top:1px solid #DCEAF2; border-bottom:1px solid #DCEAF2; background:var(--challenge-soft); }
        .challenge-stat { min-width:0; padding:0 10px; }
        .challenge-stat + .challenge-stat { border-left:1px solid #DCEAF2; }
        .challenge-stat strong { display:flex; align-items:center; justify-content:center; gap:5px; color:var(--challenge-ink); font-size:.82rem; line-height:1.1; white-space:nowrap; }
        .challenge-stat strong svg { color:var(--challenge-blue) !important; }
        .challenge-stat:nth-child(2) strong svg { color:#C97700 !important; }
        .challenge-stat span { display:block; margin-top:4px; color:var(--challenge-muted); font-size:.62rem; font-weight:700; text-align:center; }
        .challenge-card-footer { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-top:auto; padding-top:13px; }
        .challenge-leader { display:flex; min-width:0; align-items:center; gap:7px; color:var(--challenge-muted); font-size:.7rem; }
        .challenge-leader img { width:26px; height:26px; flex:0 0 auto; border-radius:50%; object-fit:cover; }
        .challenge-leader span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .challenge-cta { display:inline-flex; min-height:40px; align-items:center; justify-content:center; gap:6px; padding:0 12px; border:1px solid #1F77BE; border-radius:10px; color:#fff; background:#1F77BE; font-size:.73rem; font-weight:800; text-decoration:none; white-space:nowrap; transition:background .16s ease,transform .16s ease,box-shadow .16s ease; }
        .challenge-cta:hover { color:#fff; background:#125A96; box-shadow:0 7px 15px rgba(18,90,150,.2); transform:translateY(-1px); }
        .challenge-cta.is-muted { border-color:#D7E5EE; color:#456477; background:#F7FAFC; box-shadow:none; }
        .challenge-empty { grid-column:1/-1; padding:42px 20px; border:1px dashed #B8D7E6; border-radius:16px; color:var(--challenge-muted); background:#F7FCFD; text-align:center; }
        .challenge-empty strong { display:block; margin-bottom:6px; color:var(--challenge-ink); font-size:1rem; }
        .challenge-loading { display:flex; align-items:center; gap:8px; min-height:42px; margin:-6px 0 14px; padding:10px 12px; border:1px solid #D7E5EE; border-radius:12px; color:#61798A; background:#F8FCFD; font-size:.78rem; }
        @media (max-width:640px) {
            .challenge-page .page-heading { align-items:flex-start; flex-direction:column; gap:4px; }
            .challenge-grid { grid-template-columns:1fr; gap:13px; }
            .challenge-card-body { padding:14px; }
            .challenge-card-footer { align-items:stretch; flex-direction:column; }
            .challenge-cta { width:100%; }
        }
        @media (prefers-reduced-motion:reduce) { .challenge-card,.challenge-cover img,.challenge-cta,.challenge-progress-fill { transition:none; } }
    </style>

    <div class="page-heading">
        <div>
            <h1><x-icon name="target" size="25" />Challenge</h1>
            <p>Chọn một lộ trình thực chiến và tiến bộ từng ngày cùng cộng đồng.</p>
        </div>
    </div>

    <nav class="challenge-tabs" aria-label="Bộ lọc Challenge">
        @foreach(['active' => 'Đang chinh phục', 'completed' => 'Thành tựu', 'discover' => 'Khám phá'] as $key => $label)
            <button type="button" wire:click="setTab('{{ $key }}')" class="challenge-tab {{ $tab === $key ? 'is-active' : '' }}" aria-current="{{ $tab === $key ? 'page' : 'false' }}">
                <x-icon :name="match($key) { 'active' => 'target', 'completed' => 'trophy', default => 'compass' }" size="17" />
                {{ $label }}
                @if($key === 'active') <span class="challenge-tab-count">{{ $activeCount }}</span> @endif
                @if($key === 'completed') <span class="challenge-tab-count">{{ $completedCount }}</span> @endif
            </button>
        @endforeach
    </nav>

    <div wire:loading.flex wire:target="setTab,gotoPage" class="challenge-loading" role="status" aria-live="polite">
        <x-icon name="refresh" size="15" /> Đang cập nhật Challenge...
    </div>

    <div class="challenge-grid">
        @forelse($challenges as $challenge)
            @php
                $member = $challenge->current_member;
                $difficultyClass = match($challenge->difficulty) { 'hard' => 'is-hard', 'chaos' => 'is-chaos', default => 'is-normal' };
                $memberStatus = $member?->status;
                $isPending = in_array($memberStatus, ['pending', 'pending_payment'], true);
                $isCompleted = $tab === 'completed' || $member?->completed_at;
            @endphp
            <article class="challenge-card">
                <a href="{{ community_route('challenge.show', ['slug' => $challenge->slug ?? $challenge->id]) }}" class="challenge-cover" aria-label="Xem {{ $challenge->title }}">
                    @if($challenge->cover_path)
                        <img src="{{ asset('storage/'.$challenge->cover_path) }}" alt="">
                    @else
                        <span class="challenge-cover-fallback">
                            <span class="cover-mark"><x-icon name="layers" size="23" /></span>
                            <strong>DSCons Challenge</strong>
                            <small>BIM / MEP · Thực chiến</small>
                        </span>
                    @endif
                    <span class="challenge-cover-badge">{{ $isCompleted ? 'Đã hoàn thành' : ($member && !$isPending ? 'Đang tham gia' : 'Challenge') }}</span>
                </a>

                <div class="challenge-card-body">
                    <h2>{{ $challenge->title }}</h2>
                    <p class="challenge-description">{{ Str::limit($challenge->description ?: 'Một lộ trình thực chiến được thiết kế để bạn duy trì nhịp học và hoàn thành từng bước.', 112) }}</p>

                    <div class="challenge-meta">
                        <span class="challenge-pill {{ $difficultyClass }}"><x-icon name="target" size="14" :color="match($challenge->difficulty) { 'hard' => '#B7791F', 'chaos' => '#B42318', default => '#1F77BE' }" />{{ $challenge->difficulty_label }}</span>
                        <span class="challenge-pill"><x-icon name="users" size="14" />{{ number_format($challenge->active_members_count) }} thành viên</span>
                        <span class="challenge-pill"><x-icon name="calendar" size="14" />{{ $challenge->required_days }} ngày</span>
                    </div>

                    @if($member && !$isPending)
                        <div class="challenge-progress" aria-label="Tiến độ {{ $challenge->progress_percent }} phần trăm">
                            <div class="challenge-progress-head"><span><x-icon name="chart" size="14" />{{ $challenge->progress_completed }}/{{ $challenge->progress_total }} bài đã duyệt</span><strong>{{ $challenge->progress_percent }}%</strong></div>
                            <div class="challenge-progress-track"><div class="challenge-progress-fill" style="width:{{ $challenge->progress_percent }}%"></div></div>
                        </div>
                    @endif

                    <div class="challenge-stats">
                        <div class="challenge-stat"><strong><x-icon name="calendar" size="15" />{{ $challenge->required_days }}</strong><span>Ngày</span></div>
                        <div class="challenge-stat"><strong><x-icon name="tag" size="15" />{{ $challenge->price > 0 ? number_format($challenge->price, 0, ',', '.').'đ' : 'Miễn phí' }}</strong><span>Tham gia</span></div>
                        <div class="challenge-stat"><strong><x-icon name="clipboard" size="15" />{{ $challenge->tasks_count }}</strong><span>Bài làm</span></div>
                    </div>

                    <div class="challenge-card-footer">
                        <div class="challenge-leader">
                            @if($challenge->leader)
                                <img src="{{ $challenge->leader->avatar_url }}" alt="">
                                <span>{{ $challenge->leader->name }}</span>
                            @else
                                <span>Đội ngũ DSCons</span>
                            @endif
                        </div>
                        @if($isCompleted)
                            <a href="{{ community_route('challenge.show', ['slug' => $challenge->slug ?? $challenge->id]) }}" class="challenge-cta">Xem thành tựu <x-icon name="arrow-right" size="15" /></a>
                        @elseif($member && !$isPending)
                            <a href="{{ community_route('challenge.show', ['slug' => $challenge->slug ?? $challenge->id]) }}" class="challenge-cta">Tiếp tục Challenge <x-icon name="arrow-right" size="15" /></a>
                        @elseif($memberStatus === 'pending_payment')
                            <a href="{{ community_route('challenge.show', ['slug' => $challenge->slug ?? $challenge->id]) }}" class="challenge-cta is-muted"><x-icon name="clock" size="15" />Chờ thanh toán</a>
                        @elseif($memberStatus === 'pending')
                            <a href="{{ community_route('challenge.show', ['slug' => $challenge->slug ?? $challenge->id]) }}" class="challenge-cta is-muted"><x-icon name="clock" size="15" />Đang chờ duyệt</a>
                        @else
                            <a href="{{ community_route('challenge.show', ['slug' => $challenge->slug ?? $challenge->id]) }}" class="challenge-cta">Xem chi tiết <x-icon name="arrow-right" size="15" /></a>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="challenge-empty">
                <strong>{{ $tab === 'active' ? 'Bạn chưa bắt đầu Challenge nào' : ($tab === 'completed' ? 'Chưa có thành tựu nào' : 'Chưa có Challenge để khám phá') }}</strong>
                <span>{{ $tab === 'active' ? 'Khám phá một lộ trình phù hợp để bắt đầu tiến bộ.' : 'Các Challenge mới sẽ xuất hiện tại đây.' }}</span>
            </div>
        @endforelse
    </div>

    @if($challenges->hasPages())
        <div class="mt-6">{{ $challenges->links() }}</div>
    @endif
</div>
