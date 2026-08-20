<div class="card" style="padding:0.875rem;">
    <div class="flex justify-between items-center mb-2">
        <p class="widget-title" style="margin-bottom:0;">★ TOP EXP TUẦN</p>
        <a href="{{ community_route('leaderboard') }}" style="font-size:0.7rem; color:#1F77BE; font-weight:600;">Xem tất cả →</a>
    </div>
    @foreach($top as $i => $stat)
    @php($user = $stat->user)
    @if($user)
    <div class="flex items-center gap-2 py-1.5" style="{{ !$loop->last ? 'border-bottom:1px solid #E1E1E1;' : '' }}">
        <span style="font-size:0.75rem; font-weight:700; width:18px; text-align:center; color:{{ $i === 0 ? '#1F77BE' : ($i === 1 ? '#94A3B8' : ($i === 2 ? '#B45309' : '#5C5C66')) }};">{{ $i === 0 ? '#1' : ($i+1) }}</span>
        <img src="{{ $user->avatar_url }}" class="avatar w-7 h-7 shrink-0" alt="">
        <div style="flex:1; min-width:0;">
            <p style="font-size:0.75rem; color:#1A1A1A; font-weight:500; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $user->name }}</p>
            <p style="font-size:0.65rem; color:#5C5C66;">{{ number_format($stat->xp) }} XP · Lv.{{ $stat->level }}</p>
        </div>
        <span style="font-size:0.875rem;">{{ $user->class_emoji }}</span>
    </div>
    @endif
    @endforeach
</div>
