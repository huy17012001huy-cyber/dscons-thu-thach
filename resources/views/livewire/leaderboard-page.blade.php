<div class="leaderboard-page ds-learning-page">
    <style>
        .leaderboard-page .page-heading h1 { display:flex; align-items:center; gap:8px; }
        .leaderboard-page { --lb-blue:#1F77BE; --lb-ink:#123B59; --lb-muted:#61798A; max-width:880px; margin:0 auto; }.leaderboard-page .page-heading h1 { margin:0; color:var(--lb-ink); font-size:clamp(1.45rem,2vw,1.85rem); line-height:1.15; letter-spacing:-.035em; }.leaderboard-page .page-heading p { margin:.45rem 0 18px; color:var(--lb-muted); font-size:.86rem; line-height:1.55; }
        .lb-tabs { display:flex; gap:5px; margin-bottom:18px; padding:5px; overflow-x:auto; border:1px solid #D7E5EE; border-radius:14px; background:#fff; scrollbar-width:none; }.lb-tabs::-webkit-scrollbar{display:none}.lb-tab { min-height:42px; flex:0 0 auto; padding:0 14px; border:1px solid transparent; border-radius:10px; color:#61798A; background:transparent; font-size:.76rem; font-weight:800; cursor:pointer; }.lb-tab:hover,.lb-tab.is-active { color:#125A96; border-color:#B8D7E6; background:#E1F4F7; }
        .lb-podium { display:grid; grid-template-columns:1fr 1.15fr 1fr; align-items:end; gap:12px; margin-bottom:18px; padding:18px 16px 0; border:1px solid #D7E5EE; border-radius:18px; background:linear-gradient(180deg,#F7FCFD,#fff); }.lb-podium-user { display:flex; align-items:center; flex-direction:column; min-width:0; text-align:center; }.lb-podium-avatar { width:50px; height:50px; border:3px solid #fff; border-radius:50%; object-fit:cover; box-shadow:0 3px 10px rgba(18,59,89,.14); }.lb-podium-user.is-first .lb-podium-avatar { width:64px; height:64px; border-color:#F39402; }.lb-podium-name { max-width:100%; margin:8px 0 4px; overflow:hidden; color:var(--lb-ink); font-size:.72rem; font-weight:800; text-overflow:ellipsis; white-space:nowrap; }.lb-podium-score { margin-bottom:10px; color:#125A96; font-size:.72rem; font-weight:800; }.lb-podium-user.is-first .lb-podium-score { color:#F39402; }.lb-podium-step { display:grid; place-items:center; width:100%; height:48px; border-radius:12px 12px 0 0; color:#456477; background:#EAF5FB; font-size:1.1rem; font-weight:850; }.lb-podium-user.is-first .lb-podium-step { height:68px; color:#9A5B00; background:#FFF5DF; }.lb-podium-user.is-third .lb-podium-step { height:38px; }
        .lb-list { overflow:hidden; border:1px solid #D7E5EE; border-radius:16px; background:#fff; }.lb-row { display:flex; align-items:center; gap:11px; padding:12px 14px; border-bottom:1px solid #E7EEF1; }.lb-row:last-child { border-bottom:0; }.lb-row.is-me { background:#F7FCFD; box-shadow:inset 3px 0 0 #1F77BE; }.lb-rank { width:24px; color:#61798A; font-size:.75rem; font-weight:850; text-align:center; }.lb-rank.top-one { color:#F39402; }.lb-avatar { width:36px; height:36px; flex:0 0 auto; border-radius:50%; object-fit:cover; }.lb-user { min-width:0; flex:1; }.lb-user-name { display:flex; align-items:center; gap:6px; min-width:0; }.lb-user-name a { overflow:hidden; color:var(--lb-ink); font-size:.8rem; font-weight:800; text-overflow:ellipsis; white-space:nowrap; text-decoration:none; }.lb-user-meta { margin-top:3px; color:var(--lb-muted); font-size:.66rem; }.lb-level { display:inline-flex; min-height:20px; align-items:center; padding:0 6px; border:1px solid #B8D7E6; border-radius:999px; color:#125A96; background:#EAF5FB; font-size:.6rem; font-weight:800; }.lb-score { min-width:76px; color:#F39402; font-size:.82rem; font-weight:850; text-align:right; }.lb-score small { display:block; margin-top:2px; color:var(--lb-muted); font-size:.61rem; font-weight:600; }.lb-empty { padding:36px 20px; border:1px dashed #B8D7E6; border-radius:14px; color:var(--lb-muted); background:#F7FCFD; text-align:center; }
        .lb-tab,.lb-podium-step,.lb-user-meta,.lb-level,.lb-score { display:inline-flex; align-items:center; gap:6px; }
        .lb-podium-step { justify-content:center; }
        .lb-score { justify-content:flex-end; }
        @media (max-width:560px) { .lb-podium { gap:7px; padding-inline:8px; }.lb-podium-name { font-size:.65rem; }.lb-row { padding-inline:10px; }.lb-score { min-width:62px; }.lb-user-meta { font-size:.61rem; } }
    </style>

    @php
        $periodLabel = match($period) { 'month' => 'Tháng này', 'alltime' => 'Toàn thời gian', 'da' => 'Đá Không Cực', default => 'Tuần này' };
        $scoreFor = fn($user) => $period === 'week' || $period === 'month' ? (int) ($user->period_xp ?? 0) : (int) ($period === 'da' ? ($user->da_count ?? 0) : ($user->xp ?? 0));
        $scoreUnit = $period === 'da' ? 'Đá' : 'XP';
    @endphp

    <div class="page-heading"><h1><x-icon name="trophy" size="25" />Bảng xếp hạng</h1><p>{{ $periodLabel }} · Cùng nhìn lại những bước tiến nổi bật trong community.</p></div>
    <nav class="lb-tabs" role="tablist" aria-label="Kỳ bảng xếp hạng">
        @foreach(['week'=>['Tuần này','clock'],'month'=>['Tháng này','calendar'],'alltime'=>['Toàn thời gian','chart']] as $key => [$label,$icon])
            <button type="button" wire:click="setPeriod('{{ $key }}')" class="lb-tab {{ $period === $key ? 'is-active' : '' }}" role="tab" aria-selected="{{ $period === $key ? 'true' : 'false' }}"><x-icon name="{{ $icon }}" size="16" />{{ $label }}</button>
        @endforeach
    </nav>

    @if($top->count() >= 3)
        <section class="lb-podium" aria-label="Top 3">
            @foreach([1,0,2] as $index)
                @php $podiumUser = $top->get($index); @endphp
                <div class="lb-podium-user {{ $index === 0 ? 'is-first' : '' }} {{ $index === 2 ? 'is-third' : '' }}">
                    <img src="{{ $podiumUser->avatar_url }}" class="lb-podium-avatar" alt="">
                    <span class="lb-podium-name">{{ $podiumUser->name }}</span>
                    <span class="lb-podium-score">{{ number_format($scoreFor($podiumUser)) }} {{ $scoreUnit }}</span>
                    <span class="lb-podium-step">@if($index === 0)<x-icon name="trophy" size="20" />@elseif($index === 1)<x-icon name="award" size="18" />@else<x-icon name="flag" size="17" />@endif<span>{{ $index + 1 }}</span></span>
                </div>
            @endforeach
        </section>
    @endif

    @if($top->isNotEmpty())
        <section class="lb-list" aria-label="Danh sách xếp hạng">
            @foreach($top as $index => $user)
                <div class="lb-row {{ auth()->id() === $user->id ? 'is-me' : '' }}">
                    <span class="lb-rank {{ $index === 0 ? 'top-one' : '' }}">{{ $index + 1 }}</span>
                    <img src="{{ $user->avatar_url }}" class="lb-avatar" alt="">
                    <div class="lb-user"><div class="lb-user-name"><a href="{{ route('profile', $user->username ?? $user->id) }}">{{ $user->name }}</a><span class="lb-level"><x-icon name="flag" size="12" />Lv.{{ $user->level }}</span></div><div class="lb-user-meta"><x-icon name="users" size="13" />{{ $user->class_label }} · {{ $user->hasPremiumMembership() ? 'Premium' : 'Member' }}</div></div>
                    <div class="lb-score"><x-icon name="spark" size="15" />{{ number_format($scoreFor($user)) }}<small>{{ $scoreUnit }}</small></div>
                </div>
            @endforeach
        </section>
    @else
        <div class="lb-empty">Chưa có dữ liệu xếp hạng trong kỳ này.</div>
    @endif
</div>
