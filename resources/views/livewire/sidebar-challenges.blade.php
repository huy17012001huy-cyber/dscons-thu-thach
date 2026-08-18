<div class="card" style="padding:0.875rem;">
    <div class="flex justify-between items-center mb-2">
        <p class="widget-title" style="margin-bottom:0;">★ CHALLENGE MỞ</p>
        <a href="{{ route('challenge') }}" style="font-size:0.7rem; color:#d17856; font-weight:600;">Xem tất cả →</a>
    </div>
    @forelse($challenges as $ch)
    <a href="{{ route('challenge.show', $ch->slug ?? $ch->id) }}" class="block py-2 transition-colors" style="{{ !$loop->last ? 'border-bottom:1px solid #E1E1E1;' : '' }}">
        <div class="flex justify-between items-start">
            <p style="font-size:0.775rem; color:#1A1A1A; font-weight:500; margin-bottom:0.2rem;">{{ Str::limit($ch->title, 32) }}</p>
            <span class="badge difficulty-{{ $ch->difficulty }}" style="font-size:0.7rem; white-space:nowrap;">{{ $ch->difficulty_label }}</span>
        </div>
        <p style="font-size:0.7rem; color:#5C5C66;">{{ $ch->activeMembersCount() }}/{{ $ch->max_members }} member · {{ $ch->required_days }} ngày</p>
    </a>
    @empty
    <p style="font-size:0.8rem; color:#5C5C66; text-align:center; padding:0.5rem 0;">Không có challenge nào</p>
    @endforelse
</div>
