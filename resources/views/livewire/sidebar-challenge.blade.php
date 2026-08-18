<div class="card" style="padding:0.875rem;">
    <p class="widget-title">★ COMMUNITY CHALLENGE</p>
    @if($challenge)
    <p style="font-size:0.8rem; font-weight:600; color:#1A1A1A; margin-bottom:0.25rem;">{{ $challenge->title }}</p>
    <div class="flex justify-between mb-1">
        <span style="font-size:0.7rem; color:#5C5C66;">{{ number_format($challenge->current_value) }}/{{ number_format($challenge->target_value) }}</span>
        <span style="font-size:0.7rem; color:#078A48; font-weight:600;">{{ $challenge->progress_pct }}%</span>
    </div>
    <div class="progress-bar mb-2">
        <div class="progress-fill" style="width:{{ $challenge->progress_pct }}%;"></div>
    </div>
    <div class="flex justify-between">
        <span style="font-size:0.7rem; color:#5C5C66;">Kết thúc {{ \Carbon\Carbon::parse($challenge->week_end)->diffForHumans() }}</span>
        <span style="font-size:0.7rem; color:#d17856; font-weight:600;">+{{ $challenge->reward_xp }} EXP khi đạt</span>
    </div>
    @else
    <p style="font-size:0.8rem; color:#5C5C66; text-align:center; padding:0.5rem 0;">Không có challenge tuần này</p>
    @endif
</div>
