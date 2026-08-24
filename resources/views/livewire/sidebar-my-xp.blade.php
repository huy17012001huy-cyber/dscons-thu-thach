<div class="card sidebar-widget" style="padding:0.875rem;">
    <div class="flex items-center gap-2 mb-2">
        <img src="{{ $user->avatar_url }}" class="avatar w-8 h-8" alt="">
        <div style="flex:1; min-width:0;">
            <p style="font-size:0.8rem; font-weight:600; color:#1A1A1A; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $user->name }}</p>
            <span class="badge badge-class-{{ $user->class_color }}" style="font-size:0.7rem;"><x-icon name="{{ $user->class_icon }}" size="13" /> {{ $user->class_label }}</span>
        </div>
    </div>

    {{-- Level --}}
    <div class="flex justify-between items-center mb-1">
        <span style="font-size:0.7rem; color:#5C5C66;">Lv.{{ $user->level }} · {{ $user->job_stage }}</span>
        <span style="font-size:0.7rem; color:#d17856; font-weight:600;">{{ number_format($user->xp) }} EXP</span>
    </div>
    <div class="xp-bar mb-2">
        <div class="xp-bar-fill" style="width:{{ $progress }}%;"></div>
    </div>
    <p style="font-size:0.65rem; color:#5C5C66; text-align:right;">Còn {{ number_format($toNext) }} EXP → Lv.{{ $user->level + 1 }}</p>

    {{-- Stats --}}
    <div class="flex justify-between mt-2" style="padding-top:0.5rem; border-top:1px solid #E1E1E1;">
        <div class="text-center">
            <p style="font-size:0.9rem; font-weight:700; color:#d17856;">{{ number_format($user->aip) }}</p>
            <p style="font-size:0.7rem; color:#5C5C66;">AIP</p>
        </div>
        <div class="text-center">
            <p style="font-size:0.9rem; font-weight:700; color:#d17856;">{{ $user->da_count }}</p>
            <p style="font-size:0.7rem; color:#5C5C66;">◆ Đá</p>
        </div>
        <div class="text-center">
            <p style="font-size:0.9rem; font-weight:700; color:#DC2626;">{{ $user->streak }}</p>
            <p style="font-size:0.7rem; color:#5C5C66;"> Streak</p>
        </div>
    </div>
</div>
