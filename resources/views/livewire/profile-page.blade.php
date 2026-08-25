<div class="profile-page">
    <style>
        .profile-page { width:min(100%, 930px); margin:0 auto; color:#123B59; }
        .profile-shell { display:grid; gap:1rem; }
        .profile-card { border:1px solid #D7E5EE; border-radius:18px; background:#fff; box-shadow:0 5px 18px rgba(18,59,89,.045); }
        .profile-hero { padding:1.2rem; }
        .profile-hero-top { display:flex; align-items:flex-start; gap:1rem; }
        .profile-avatar-wrap { position:relative; width:86px; height:86px; flex:0 0 auto; }
        .profile-avatar-wrap img { width:86px; height:86px; border:3px solid #E1F4F7; border-radius:50%; object-fit:cover; }
        .profile-avatar-online { position:absolute; right:2px; bottom:2px; width:15px; height:15px; border:3px solid #fff; border-radius:50%; background:#1F77BE; }
        .profile-identity { min-width:0; flex:1; }
        .profile-identity h1 { margin:0; overflow:hidden; color:#123B59; font-size:1.4rem; font-weight:850; letter-spacing:-.04em; text-overflow:ellipsis; white-space:nowrap; }
        .profile-handle { margin-top:.25rem; color:#61798A; font-size:.76rem; }
        .profile-bio { max-width:620px; margin:.55rem 0 0; color:#456477; font-size:.8rem; line-height:1.55; }
        .profile-meta { display:flex; flex-wrap:wrap; gap:.4rem .75rem; margin-top:.65rem; color:#61798A; font-size:.68rem; }
        .profile-meta span { display:inline-flex; align-items:center; gap:.3rem; }
        .profile-actions { display:flex; gap:.45rem; flex:0 0 auto; }
        .profile-action { display:inline-flex; min-height:38px; align-items:center; justify-content:center; gap:.35rem; padding:0 .75rem; border:1px solid #D7E5EE; border-radius:10px; color:#456477; background:#fff; font-size:.72rem; font-weight:800; text-decoration:none; cursor:pointer; }
        .profile-action:hover { border-color:#1F77BE; color:#125A96; background:#F2F9FC; }
        .profile-action.is-primary { border-color:#1F77BE; color:#fff; background:#1F77BE; }
        .profile-action.is-primary:hover { color:#fff; background:#125A96; }
        .profile-badges { display:flex; flex-wrap:wrap; gap:.4rem; margin-top:.8rem; }
        .profile-badge { display:inline-flex; min-height:26px; align-items:center; gap:.3rem; padding:0 .55rem; border:1px solid #B8D7E6; border-radius:999px; color:#125A96; background:#EAF5FB; font-size:.67rem; font-weight:800; }
        .profile-badge.is-accent { border-color:#F3D596; color:#9A5B00; background:#FFF5DF; }
        .profile-level { display:grid; grid-template-columns:74px minmax(0,1fr); align-items:center; gap:1rem; padding:1rem 1.1rem; }
        .profile-level-medal { display:grid; place-items:center; width:68px; height:68px; border:3px solid #F3D596; border-radius:50%; color:#fff; background:#F0B429; font-size:1.45rem; font-weight:900; box-shadow:0 0 0 5px #FFF7E6; }
        .profile-level-copy { min-width:0; }
        .profile-level-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; }
        .profile-level-head strong { color:#123B59; font-size:.86rem; font-weight:850; }
        .profile-level-head span { color:#61798A; font-size:.68rem; }
        .profile-progress { height:12px; margin-top:.65rem; overflow:hidden; border-radius:999px; background:#E7EEF1; }
        .profile-progress span { display:block; height:100%; border-radius:inherit; background:linear-gradient(90deg,#F0B429,#F39402); }
        .profile-level-foot { display:flex; justify-content:space-between; gap:.5rem; margin-top:.4rem; color:#61798A; font-size:.67rem; }
        .profile-section { padding:1rem 1.1rem; }
        .profile-section-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; margin-bottom:.7rem; }
        .profile-section-head h2 { display:flex; align-items:center; gap:.4rem; margin:0; color:#123B59; font-size:.95rem; font-weight:850; }
        .profile-section-head span { color:#61798A; font-size:.68rem; }
        .profile-achievements { display:flex; flex-wrap:wrap; gap:.5rem; }
        .profile-achievement { display:inline-flex; align-items:center; gap:.4rem; min-height:38px; padding:0 .7rem; border-radius:10px; color:#7A4A08; background:#FFF5DF; font-size:.72rem; font-weight:800; }
        .profile-achievement svg { color:#F39402; }
        .profile-tabs { display:flex; align-items:center; justify-content:space-between; gap:.8rem; padding:5px; border:1px solid #D7E5EE; border-radius:12px; background:#F7FBFD; }
        .profile-tab-group { display:flex; gap:3px; }
        .profile-tab { min-height:34px; padding:0 .7rem; border:1px solid transparent; border-radius:8px; color:#61798A; background:transparent; font-size:.72rem; font-weight:800; cursor:pointer; }
        .profile-tab.is-active { border-color:#B8D7E6; color:#125A96; background:#fff; }
        .profile-period { display:flex; gap:3px; }
        .profile-period span { display:inline-flex; min-height:28px; align-items:center; padding:0 .5rem; border-radius:7px; color:#61798A; font-size:.65rem; font-weight:800; }
        .profile-period span.is-active { color:#125A96; background:#E1F4F7; }
        .profile-stat-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.5rem; margin-top:.75rem; }
        .profile-stat { min-height:74px; padding:.65rem; border:1px solid #E7EEF1; border-radius:12px; background:#fff; }
        .profile-stat-icon { display:grid; place-items:center; width:27px; height:27px; margin-bottom:.35rem; border-radius:9px; color:#125A96; background:#EAF5FB; }
        .profile-stat strong { display:block; color:#123B59; font-size:.9rem; font-weight:850; }
        .profile-stat span { display:block; margin-top:.1rem; color:#61798A; font-size:.63rem; }
        .profile-heatmap { margin-top:.7rem; padding:.8rem; overflow-x:auto; border-radius:13px; background:#F5F8FA; }
        .profile-heatmap-months { display:flex; gap:0; min-width:max-content; margin-left:28px; }
        .profile-heatmap-months span { width:13px; min-height:14px; color:#61798A; font-size:.52rem; white-space:nowrap; }
        .profile-heatmap-body { display:flex; min-width:max-content; gap:4px; }
        .profile-heatmap-days { display:grid; grid-template-rows:repeat(7,11px); gap:2px; width:24px; flex:0 0 auto; }
        .profile-heatmap-days span { color:#61798A; font-size:.52rem; }
        .profile-heatmap-cols { display:flex; gap:2px; }
        .profile-heatmap-col { display:grid; grid-template-rows:repeat(7,11px); gap:2px; }
        .profile-heatmap-cell { width:11px; height:11px; border-radius:3px; background:#E7EEF1; }
        .profile-heatmap-cell.level-1 { background:#C8EAF0; }.profile-heatmap-cell.level-2 { background:#8DD4DF; }.profile-heatmap-cell.level-3 { background:#58B9C7; }.profile-heatmap-cell.level-4 { background:#1F77BE; }
        .profile-activity-foot { margin:.55rem 0 0; color:#61798A; font-size:.67rem; }
        .profile-community-list { display:grid; gap:.45rem; }
        .profile-community { display:flex; align-items:center; gap:.6rem; padding:.55rem .1rem; }
        .profile-community img, .profile-community-fallback { width:34px; height:34px; border-radius:10px; object-fit:cover; }.profile-community-fallback { display:grid; place-items:center; color:#125A96; background:#E1F4F7; font-size:.75rem; font-weight:850; }
        .profile-community strong { display:block; color:#123B59; font-size:.78rem; font-weight:800; }.profile-community span { display:block; margin-top:.15rem; color:#61798A; font-size:.66rem; }
        .profile-empty { padding:1.1rem; border:1px dashed #B8D7E6; border-radius:12px; color:#61798A; background:#F7FCFD; font-size:.74rem; text-align:center; }
        .profile-posts { display:grid; gap:.65rem; }
        @media(max-width:700px){ .profile-hero-top { flex-wrap:wrap; }.profile-identity { min-width:calc(100% - 102px); }.profile-actions { width:100%; }.profile-action { flex:1; }.profile-stat-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }.profile-level { grid-template-columns:56px minmax(0,1fr); gap:.75rem; padding:.85rem; }.profile-level-medal { width:54px; height:54px; font-size:1.1rem; }.profile-level-head { align-items:flex-start; flex-direction:column; gap:.15rem; } }
    </style>

    @php
        $totalContributions = array_sum($contributions);
        $activeDays = count($contributions);
        $maxXp = max(1, max($contributions ?: [1]));
        $colors = ['#E7EEF1', '#C8EAF0', '#8DD4DF', '#58B9C7', '#1F77BE'];
        $todayDate = now();
        $endOfWeek = $todayDate->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
        $startDate = $endOfWeek->copy()->subWeeks(52)->startOfWeek(\Carbon\Carbon::MONDAY);
        $weeks = [];
        $monthLabels = [];
        $cursor = $startDate->copy();
        $weekIdx = 0;
        while ($cursor->lte($endOfWeek)) {
            if ($cursor->day <= 7) $monthLabels[$weekIdx] = $cursor->locale('vi')->isoFormat('MMM');
            $week = [];
            for ($d = 0; $d < 7; $d++) {
                $date = $cursor->copy()->addDays($d);
                if ($date->lt($startDate) || $date->gt($endOfWeek)) { $week[] = null; continue; }
                $key = $date->format('Y-m-d');
                $xp = (int) ($contributions[$key] ?? 0);
                $level = $xp > 0 ? match (true) { $xp / $maxXp >= .75 => 4, $xp / $maxXp >= .5 => 3, $xp / $maxXp >= .25 => 2, default => 1 } : 0;
                $week[] = ['level' => $level, 'title' => $xp > 0 ? number_format($xp).' EXP' : 'Chưa có hoạt động'];
            }
            $weeks[] = $week; $cursor->addWeek(); $weekIdx++;
        }
    @endphp

    @if(session('profile_saved'))<div class="profile-card" style="padding:.75rem 1rem;color:#18794E;background:#ECFDF3;border-color:#B7E4C7;font-size:.75rem;font-weight:700;">{{ session('profile_saved') }}</div>@endif

    <div class="profile-shell">
        <section class="profile-card profile-hero">
            <div class="profile-hero-top">
                <div class="profile-avatar-wrap"><img src="{{ $profileUser->avatar_url }}" alt="Ảnh đại diện của {{ $profileUser->name }}"><span class="profile-avatar-online" title="Đang hoạt động"></span></div>
                <div class="profile-identity">
                    <h1>{{ $profileUser->name }}</h1>
                    <div class="profile-handle">{{ '@'.($profileUser->username ?: 'thanh-vien') }}</div>
                    @if($profileUser->bio)<p class="profile-bio">{{ $profileUser->bio }}</p>@endif
                    <div class="profile-meta">
                        @if($profileUser->location)<span><x-icon name="compass" size="13" /> {{ $profileUser->location }}</span>@endif
                        <span><x-icon name="calendar" size="13" /> Tham gia {{ $profileUser->created_at?->diffForHumans() }}</span>
                        <span><x-icon name="check-circle" size="13" /> Đang hoạt động</span>
                    </div>
                    <div class="profile-badges">
                        <span class="profile-badge"><x-icon name="award" size="14" />{{ $currentMembership?->plan ?: ($currentMembership?->tier ? ucfirst($currentMembership->tier) : 'Member') }}</span>
                        <span class="profile-badge is-accent"><x-icon name="trophy" size="14" />{{ $profileUser->job_stage }}</span>
                    </div>
                </div>
                <div class="profile-actions">
                    @if(auth()->id() === $profileUser->id)<a class="profile-action is-primary" role="button" aria-label="Chỉnh sửa hồ sơ" href="{{ route('profile.edit') }}"><x-icon name="settings" size="15" color="#fff" /> Chỉnh sửa hồ sơ</a>@else<a class="profile-action" href="{{ route('messages') }}?user={{ $profileUser->id }}"><x-icon name="chat" size="15" /> Nhắn tin</a>@endif
                    <button type="button" class="profile-action" onclick="copyCommunityLink(window.location.href)"><x-icon name="share" size="15" /> Chia sẻ</button>
                </div>
            </div>
        </section>

        <section class="profile-card profile-level">
            <div class="profile-level-medal">{{ $profileUser->level }}</div>
            <div class="profile-level-copy">
                <div class="profile-level-head"><strong>Level {{ $profileUser->level }} · {{ $profileUser->job_stage }}</strong><span>{{ number_format($profileUser->xp) }} EXP</span></div>
                <div class="profile-progress"><span style="width:{{ $xpProgress }}%"></span></div>
                <div class="profile-level-foot"><span>Tiến độ hiện tại</span><span>Còn {{ number_format($toNext) }} EXP để lên level tiếp theo</span></div>
            </div>
        </section>

        <section class="profile-card profile-section">
            <div class="profile-section-head"><h2><x-icon name="award" size="18" /> Thành tựu</h2><span>{{ $badges->count() }} thành tựu</span></div>
            @if($badges->isNotEmpty())<div class="profile-achievements">@foreach($badges as $badge)<span class="profile-achievement"><x-icon name="trophy" size="16" />{{ $badge->badge->name }}</span>@endforeach</div>@else<div class="profile-empty">Bạn chưa có thành tựu nào. Hãy hoàn thành hoạt động đầu tiên trong community.</div>@endif
        </section>

        <section class="profile-card profile-section">
            <div class="profile-tabs">
                <div class="profile-tab-group"><button type="button" wire:click="setTab('posts')" class="profile-tab {{ $tab === 'posts' ? 'is-active' : '' }}">Tổng quan</button><button type="button" wire:click="setTab('cot')" class="profile-tab {{ $tab === 'cot' ? 'is-active' : '' }}">Hoạt động</button>@if(auth()->id() === $profileUser->id)<button type="button" wire:click="setTab('bookmarks')" class="profile-tab {{ $tab === 'bookmarks' ? 'is-active' : '' }}">Đã lưu</button>@endif</div>
                <div class="profile-period"><span class="is-active">1 năm</span></div>
            </div>
            <div class="profile-stat-grid">
                <div class="profile-stat"><span class="profile-stat-icon"><x-icon name="file-text" size="15" /></span><strong>{{ $profileUser->posts()->count() }}</strong><span>Bài viết</span></div>
                <div class="profile-stat"><span class="profile-stat-icon"><x-icon name="chat" size="15" /></span><strong>{{ $commentCount }}</strong><span>Bình luận</span></div>
                <div class="profile-stat"><span class="profile-stat-icon"><x-icon name="spark" size="15" /></span><strong>{{ number_format($profileUser->xp) }}</strong><span>Tổng EXP</span></div>
                <div class="profile-stat"><span class="profile-stat-icon"><x-icon name="calendar" size="15" /></span><strong>{{ $activeDays }}</strong><span>Ngày hoạt động</span></div>
            </div>
            <div class="profile-heatmap" aria-label="Biểu đồ hoạt động một năm">
                <div class="profile-heatmap-months">@foreach($weeks as $wi => $week)<span>{{ $monthLabels[$wi] ?? '' }}</span>@endforeach</div>
                <div class="profile-heatmap-body"><div class="profile-heatmap-days"><span></span><span>T2</span><span></span><span>T4</span><span></span><span>T6</span><span></span></div><div class="profile-heatmap-cols">@foreach($weeks as $week)<div class="profile-heatmap-col">@foreach($week as $day)<span class="profile-heatmap-cell level-{{ $day['level'] ?? 0 }}" title="{{ $day['title'] ?? 'Chưa có hoạt động' }}"></span>@endforeach</div>@endforeach</div></div>
            </div>
            <p class="profile-activity-foot">{{ number_format($totalContributions) }} EXP trong 12 tháng qua · {{ $activeDays }} ngày có hoạt động</p>
        </section>

        <section class="profile-card profile-section">
            <div class="profile-section-head"><h2><x-icon name="users" size="18" /> Cộng đồng đang tham gia</h2><span>{{ $activeCommunities->count() }} community</span></div>
            @if($activeCommunities->isNotEmpty())<div class="profile-community-list">@foreach($activeCommunities as $community)<div class="profile-community">@if($community->logo_path)<img src="{{ asset('storage/'.$community->logo_path) }}" alt="">@else<span class="profile-community-fallback">{{ strtoupper(substr($community->name,0,1)) }}</span>@endif<div><strong>{{ $community->name }}</strong><span>Hồ sơ và hoạt động được quản lý riêng trong community này</span></div></div>@endforeach</div>@else<div class="profile-empty">Bạn chưa tham gia community nào.</div>@endif
        </section>

        <section class="profile-card profile-section">
            <div class="profile-section-head"><h2><x-icon name="file-text" size="18" /> {{ $tab === 'cot' ? 'Bài CỐT gần đây' : ($tab === 'bookmarks' ? 'Bài đã lưu' : 'Bài viết gần đây') }}</h2></div>
            <div class="profile-posts">
                @if($tab === 'posts' && $posts)
                    @forelse($posts as $post)<livewire:post-card :post="$post" :key="'prof-'.$post->id" />@empty<div class="profile-empty">Bạn chưa đăng bài nào. Hãy chia sẻ một kinh nghiệm hoặc câu hỏi đầu tiên.</div>@endforelse
                    <div>{{ $posts->links() }}</div>
                @elseif($tab === 'cot' && $cotPosts)
                    @forelse($cotPosts as $post)<livewire:post-card :post="$post" :key="'cot-prof-'.$post->id" />@empty<div class="profile-empty">Chưa có bài CỐT nào.</div>@endforelse
                    <div>{{ $cotPosts->links() }}</div>
                @elseif($tab === 'bookmarks' && $bookmarkedPosts)
                    @forelse($bookmarkedPosts as $post)<livewire:post-card :post="$post" :key="'bm-'.$post->id" />@empty<div class="profile-empty">Bạn chưa lưu bài viết nào.</div>@endforelse
                    <div>{{ $bookmarkedPosts->links() }}</div>
                @endif
            </div>
        </section>
    </div>
</div>
