<div>
    <h1 style="font-size:1.25rem; font-weight:800; color:#1A1A1A; margin-bottom:1rem;">★ Leaderboard</h1>

    <div class="tab-nav mb-6">
        @foreach(['week'=>'EXP Tuần','month'=>'EXP Tháng','alltime'=>'EXP All-time','da'=>'◆ Đá Không Cực'] as $p => $l)
        <button wire:click="setPeriod('{{ $p }}')" class="tab-item {{ $period === $p ? 'active' : '' }}">{{ $l }}</button>
        @endforeach
    </div>

    {{-- Podium Top 3 --}}
    @if($top->count() >= 3)
    <div class="card mb-6">
        <div class="flex items-end justify-center gap-4" style="padding:1.5rem 0;">
            {{-- 2nd --}}
            @php $second = $top->get(1); @endphp
            <div class="text-center">
                <img src="{{ $second->avatar_url }}" class="avatar w-12 h-12 mx-auto mb-2" alt="">
                <p style="font-size:0.75rem; color:#1A1A1A; font-weight:600;">{{ $second->name }}</p>
                <span class="badge badge-class-{{ $second->class_color }}" style="font-size:0.7rem;">{{ $second->class_emoji }}</span>
                <div style="background:#EEECE9; border-radius:0.5rem 0.5rem 0 0; padding:0.75rem 1.5rem; margin-top:0.5rem; height:70px; display:flex; align-items:center; justify-content:center;">
                    <p style="font-size:1.25rem; font-weight:800; color:#5C5C66;">2</p>
                </div>
            </div>
            {{-- 1st --}}
            @php $first = $top->get(0); @endphp
            <div class="text-center">
                <p style="font-size:1.5rem; margin-bottom:0.25rem;">#1</p>
                <img src="{{ $first->avatar_url }}" class="avatar w-14 h-14 mx-auto mb-2" style="border:2px solid #d17856;" alt="">
                <p style="font-size:0.8rem; color:#d17856; font-weight:700;">{{ $first->name }}</p>
                <span class="badge badge-class-{{ $first->class_color }}" style="font-size:0.7rem;">{{ $first->class_emoji }}</span>
                <div style="background:#F0EDE8; border:1px solid #d17856; border-radius:0.5rem 0.5rem 0 0; padding:0.75rem 1.5rem; margin-top:0.5rem; height:90px; display:flex; align-items:center; justify-content:center;">
                    <p style="font-size:1.5rem; font-weight:800; color:#d17856;">1</p>
                </div>
            </div>
            {{-- 3rd --}}
            @php $third = $top->get(2); @endphp
            <div class="text-center">
                <img src="{{ $third->avatar_url }}" class="avatar w-12 h-12 mx-auto mb-2" alt="">
                <p style="font-size:0.75rem; color:#1A1A1A; font-weight:600;">{{ $third->name }}</p>
                <span class="badge badge-class-{{ $third->class_color }}" style="font-size:0.7rem;">{{ $third->class_emoji }}</span>
                <div style="background:#EEECE9; border-radius:0.5rem 0.5rem 0 0; padding:0.75rem 1.5rem; margin-top:0.5rem; height:55px; display:flex; align-items:center; justify-content:center;">
                    <p style="font-size:1.1rem; font-weight:800; color:#B45309;">3</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card">
        @foreach($top as $i => $user)
        <div class="flex items-center gap-3 py-3 {{ !$loop->last ? 'border-b' : '' }}" style="{{ !$loop->last ? 'border-color:#E1E1E1;' : '' }}">
            <span style="font-size:0.8rem; font-weight:700; width:28px; text-align:center; color:{{ $i === 0 ? '#d17856' : ($i === 1 ? '#94A3B8' : ($i === 2 ? '#B45309' : '#5C5C66')) }};">{{ $i + 1 }}</span>
            <img src="{{ $user->avatar_url }}" class="avatar w-9 h-9 shrink-0" alt="">
            <div style="flex:1; min-width:0;">
                <div class="flex items-center gap-2">
                    <a href="{{ route('profile', $user->username ?? $user->id) }}" style="font-size:0.875rem; font-weight:600; color:#1A1A1A;">{{ $user->name }}</a>
                    <span class="badge badge-class-{{ $user->class_color }}" style="font-size:0.7rem;">{{ $user->class_emoji }} {{ $user->class_label }}</span>
                    <span class="level-badge">Lv.{{ $user->level }}</span>
                </div>
            </div>
            <div class="text-right">
                @if($period === 'da')
                <p style="font-size:0.9rem; font-weight:700; color:#d17856;">◆ {{ $user->da_count }}</p>
                @elseif(in_array($period, ['week', 'month']))
                <p style="font-size:0.875rem; font-weight:700; color:#d17856;">{{ number_format($user->period_xp ?? 0) }}</p>
                <p style="font-size:0.65rem; color:#5C5C66;">EXP {{ $period === 'week' ? 'tuần' : 'tháng' }}</p>
                @else
                <p style="font-size:0.875rem; font-weight:700; color:#d17856;">{{ number_format($user->xp) }}</p>
                <p style="font-size:0.65rem; color:#5C5C66;">EXP</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
