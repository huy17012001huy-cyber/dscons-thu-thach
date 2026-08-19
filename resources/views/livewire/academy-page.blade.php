<div>
    <div class="page-heading">
        <div>
            <h1 style="font-size:1.25rem; font-weight:800; color:#1A1A1A;">▦ Khóa học</h1>
            <p style="font-size:0.8rem; color:#5C5C66;">Khóa học chuyên sâu theo từng trụ cột</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="filter-bar filter-bar-scroll">
        <div class="flex gap-1">
            @foreach(['offer'=>'🔥 Offer','traffic'=>'✨ Traffic','conversion'=>'🎯 Conversion','delivery'=>'⚙️ Delivery','continuity'=>'🔗 Continuity'] as $key => $label)
            <button wire:click="setPillar('{{ $key }}')" class="badge {{ $pillar === $key ? 'badge-pillar-'.$key : '' }}" style="cursor:pointer; padding:0.25rem 0.625rem; font-size:0.75rem; {{ $pillar === $key ? '' : 'background:#EEECE9; color:#5C5C66;' }}">{{ $label }}</button>
            @endforeach
        </div>
        <div class="flex gap-1 ml-auto">
            @foreach([''=>'Tất cả','basic'=>'Cơ bản','advanced'=>'Nâng cao','expert'=>'Chuyên gia'] as $d => $l)
            <button wire:click="$set('difficulty','{{ $d }}')" class="badge" style="cursor:pointer; padding:0.25rem 0.625rem; font-size:0.7rem; {{ $difficulty === $d ? 'background:#E8F5E9; color:#1B5E20;' : 'background:#EEECE9; color:#5C5C66;' }}">{{ $l }}</button>
            @endforeach
        </div>
    </div>

    {{-- Course grid --}}
    <div class="grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
        @forelse($courses as $course)
        <div class="card course-card">
            @if($course->thumbnail)
            <div class="course-thumb" style="background:#EEECE9; border-radius:0.5rem; margin-bottom:0.75rem; overflow:hidden;">
                <img src="{{ asset('storage/' . $course->thumbnail) }}" alt="" style="width:100%; height:100%; object-fit:cover;">
            </div>
            @else
            <div class="course-thumb" style="background:#F0EDE8; border-radius:0.5rem; margin-bottom:0.75rem; display:flex; align-items:center; justify-content:center;">
                <span style="font-size:2.5rem;">▦</span>
            </div>
            @endif

            <div class="flex items-center gap-2 mb-2">
                <span class="badge badge-pillar-{{ $course->pillar }}" style="font-size:0.65rem;">{{ ucfirst($course->pillar) }}</span>
                <span class="badge" style="font-size:0.65rem; {{ match($course->difficulty) { 'basic' => 'background:#D1FAE5; color:#065F46;', 'advanced' => 'background:#E8F5E9; color:#1B5E20;', 'expert' => 'background:#FEE2E2; color:#991B1B;', default => 'background:#EEECE9; color:#5C5C66;' } }}">{{ ucfirst($course->difficulty) }}</span>
                @if($course->min_level > 1)
                <span class="level-badge">Lv.{{ $course->min_level }}+</span>
                @endif
            </div>

            <h3 style="font-size:0.9rem; font-weight:700; color:#1A1A1A; margin-bottom:0.375rem; line-height:1.3;">{{ $course->title }}</h3>

            @if($course->description)
            <p style="font-size:0.8rem; color:#5C5C66; margin-bottom:0.75rem; line-height:1.4;">{{ Str::limit($course->description, 100) }}</p>
            @endif

            <div class="flex items-center gap-3 mb-3">
                <div>
                    <p style="font-size:0.75rem; font-weight:700; color:#1A1A1A;">{{ $course->modules_count ?? $course->modules()->count() }}</p>
                    <p style="font-size:0.65rem; color:#5C5C66;">Module</p>
                </div>
                <div>
                    <p style="font-size:0.75rem; font-weight:700; color:#d17856;">+{{ $course->xp_reward }} XP</p>
                    <p style="font-size:0.65rem; color:#5C5C66;">Phần thưởng</p>
                </div>
                <div>
                    <p style="font-size:0.75rem; font-weight:700; color:#1A1A1A;">{{ $course->enrollments_count }}</p>
                    <p style="font-size:0.65rem; color:#5C5C66;">Học viên</p>
                </div>
            </div>

            <div class="card-footer flex items-center justify-between">
                @if($course->price > 0)
                <p style="font-size:0.9rem; font-weight:700; color:#d17856;">{{ number_format($course->price, 0, ',', '.') }}đ</p>
                @else
                <p style="font-size:0.8rem; font-weight:600; color:#059669;">Miễn phí</p>
                @endif
                <a href="{{ route('academy.show', $course->id) }}" class="btn btn-primary" style="font-size:0.8rem; padding:0.4rem 0.75rem;">Xem khóa học</a>
            </div>
        </div>
        @empty
        <div class="card empty-state" style="grid-column:1/-1;">
            <p style="font-size:2rem; margin-bottom:0.5rem;">▦</p>
            <p style="color:#5C5C66;">Chưa có khóa học nào{{ $pillar ? ' cho trụ cột này' : '' }}</p>
        </div>
        @endforelse
    </div>
</div>
