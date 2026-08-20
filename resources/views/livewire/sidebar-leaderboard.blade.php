<section class="rp-card leaderboard-card" style="padding:15px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px;">
        <h2 style="margin:0;font-size:12px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;color:var(--text);">Bảng xếp hạng · 30 ngày</h2>
        <a href="{{ community_route('leaderboard') }}" style="font-size:11px;color:var(--green);font-weight:750;text-decoration:none;white-space:nowrap;">Xem tất cả</a>
    </div>
    @forelse($top as $i => $stat)
        @php($user = $stat->user)
        @if($user)
        <a href="{{ route('profile', $user->username ?? $user->id) }}" style="min-height:44px;display:flex;align-items:center;gap:8px;padding:7px 0;text-decoration:none;border-top:{{ $loop->first ? '0' : '1px solid var(--border)' }};">
            <span style="width:21px;height:21px;display:grid;place-items:center;border-radius:50%;font-size:11px;font-weight:800;background:{{ $i === 0 ? '#FBE9B6' : ($i === 1 ? '#E7EDF1' : ($i === 2 ? '#F6DEC8' : 'transparent')) }};color:{{ $i === 0 ? '#A06B00' : ($i === 1 ? '#61798A' : ($i === 2 ? '#A9581C' : 'var(--text-muted)')) }};">{{ $i + 1 }}</span>
            <img src="{{ $user->avatar_url }}" alt="" style="width:27px;height:27px;border-radius:50%;object-fit:cover;">
            <span style="min-width:0;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px;font-weight:700;color:var(--text);">{{ $user->name }}</span>
            <span style="font-size:11px;font-weight:800;color:var(--green);white-space:nowrap;">{{ number_format($stat->xp) }} XP</span>
        </a>
        @endif
    @empty
        <p style="padding:10px 0 2px;margin:0;font-size:12px;line-height:1.5;color:var(--text-muted);">Chưa có dữ liệu xếp hạng trong community này.</p>
    @endforelse
</section>
