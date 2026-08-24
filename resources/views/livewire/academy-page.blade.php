<div class="academy-page ds-learning-page">
    <style>
        .academy-page { --academy-blue:#1F77BE; --academy-ink:#123B59; --academy-muted:#61798A; }
        .academy-page .page-heading { margin-bottom:18px; }
        .academy-page .page-heading h1 { display:flex; align-items:center; gap:8px; margin:0; color:var(--academy-ink); font-size:clamp(1.45rem,2vw,1.85rem); line-height:1.15; letter-spacing:-.035em; }
        .academy-page .page-heading p { margin:.45rem 0 0; color:var(--academy-muted); font-size:.86rem; line-height:1.55; }
        .academy-filter { display:flex; gap:5px; margin-bottom:20px; padding:5px; overflow-x:auto; border:1px solid #D7E5EE; border-radius:14px; background:#fff; scrollbar-width:none; }
        .academy-filter::-webkit-scrollbar { display:none; }
        .academy-filter-group { display:flex; flex:0 0 auto; gap:4px; }
        .academy-filter button { display:inline-flex; align-items:center; gap:5px; min-height:40px; padding:0 12px; border:1px solid transparent; border-radius:10px; color:#61798A; background:transparent; font-size:.76rem; font-weight:750; cursor:pointer; white-space:nowrap; transition:background .16s ease,border-color .16s ease,color .16s ease; }
        .academy-filter button:hover { color:#125A96; background:#EEF7F9; }
        .academy-filter button.is-active { color:#125A96; border-color:#B8D7E6; background:#E1F4F7; }
        .academy-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(285px,1fr)); gap:16px; }
        .academy-card { display:flex; min-width:0; flex-direction:column; overflow:hidden; border:1px solid #D7E5EE; border-radius:18px; background:#fff; box-shadow:0 3px 10px rgba(18,59,89,.045); transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease; }
        .academy-card:hover { transform:translateY(-3px); border-color:#8FB9CB; box-shadow:0 14px 28px rgba(18,59,89,.11); }
        .academy-thumb { position:relative; aspect-ratio:16/9; overflow:hidden; background:#E1F4F7; }
        .academy-thumb img { width:100%; height:100%; object-fit:cover; transition:transform .35s ease; }
        .academy-card:hover .academy-thumb img { transform:scale(1.035); }
        .academy-thumb-fallback { display:grid; place-items:center; width:100%; height:100%; color:#125A96; font-size:1.3rem; font-weight:850; letter-spacing:.18em; }
        .academy-featured { position:absolute; right:12px; top:12px; padding:5px 8px; border-radius:999px; color:#fff; background:#F39402; font-size:.64rem; font-weight:800; }
        .academy-card-body { display:flex; flex:1; flex-direction:column; padding:15px; }
        .academy-badges { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:10px; }
        .academy-badge { display:inline-flex; min-height:25px; align-items:center; gap:5px; padding:0 8px; border:1px solid #D7E5EE; border-radius:999px; color:#456477; background:#F7FAFC; font-size:.66rem; font-weight:750; }
        .academy-badge.pillar { color:#125A96; border-color:#B8D7E6; background:#EAF5FB; }
        .academy-badge.difficulty-basic { color:#125A96; background:#EAF5FB; border-color:#B8D7E6; }
        .academy-badge.difficulty-advanced { color:#9A5B00; background:#FFF5DF; border-color:#F3D596; }
        .academy-badge.difficulty-expert { color:#9B3D31; background:#FFF0EE; border-color:#F0C0B8; }
        .academy-card h2 { margin:0; color:var(--academy-ink); font-size:1rem; line-height:1.3; letter-spacing:-.015em; }
        .academy-description { min-height:42px; margin:8px 0 13px; color:var(--academy-muted); font-size:.78rem; line-height:1.55; }
        .academy-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; padding:11px 0; border-top:1px solid #E7EEF1; border-bottom:1px solid #E7EEF1; }
        .academy-stat strong { display:flex; align-items:center; justify-content:center; gap:5px; color:var(--academy-ink); font-size:.8rem; line-height:1.1; }
        .academy-stat span { display:block; margin-top:3px; color:var(--academy-muted); font-size:.64rem; }
        .academy-progress { margin-top:12px; }
        .academy-progress-head { display:flex; justify-content:space-between; margin-bottom:6px; color:var(--academy-muted); font-size:.69rem; }
        .academy-progress-head span { display:inline-flex; align-items:center; gap:5px; }
        .academy-progress-head strong { color:#125A96; }
        .academy-progress-track { height:7px; overflow:hidden; border-radius:999px; background:#E7F0F4; }
        .academy-progress-fill { height:100%; border-radius:inherit; background:#1F77BE; }
        .academy-card-footer { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-top:auto; padding-top:13px; }
        .academy-price { display:inline-flex; align-items:center; gap:5px; color:#F39402; font-size:.9rem; font-weight:850; }
        .academy-price.is-free { color:#125A96; font-size:.78rem; }
        .academy-cta { display:inline-flex; min-height:40px; align-items:center; justify-content:center; gap:6px; padding:0 12px; border:1px solid #1F77BE; border-radius:10px; color:#fff; background:#1F77BE; font-size:.73rem; font-weight:800; text-decoration:none; white-space:nowrap; transition:background .16s ease,transform .16s ease,box-shadow .16s ease; }
        .academy-cta:hover { color:#fff; background:#125A96; box-shadow:0 7px 15px rgba(18,90,150,.2); transform:translateY(-1px); }
        .academy-empty { grid-column:1/-1; padding:42px 20px; border:1px dashed #B8D7E6; border-radius:16px; color:var(--academy-muted); background:#F7FCFD; text-align:center; }
        .academy-empty strong { display:block; margin-bottom:6px; color:var(--academy-ink); font-size:1rem; }
        .academy-loading { display:flex; align-items:center; gap:8px; min-height:42px; margin:-6px 0 14px; padding:10px 12px; border:1px solid #D7E5EE; border-radius:12px; color:#61798A; background:#F8FCFD; font-size:.78rem; }
        @media (max-width:640px) { .academy-grid { grid-template-columns:1fr; gap:13px; } .academy-card-footer { align-items:stretch; flex-direction:column; } .academy-cta { width:100%; } }
        @media (prefers-reduced-motion:reduce) { .academy-card,.academy-thumb img,.academy-cta { transition:none; } }
    </style>
    @php $pillars = brand()->pillarProfiles(); @endphp

    <div class="page-heading">
        <h1><x-icon name="graduation" size="25" />Khóa học</h1>
        <p>Những chương trình chuyên sâu giúp bạn xây năng lực theo từng trụ cột.</p>
    </div>

    <div class="academy-filter" role="toolbar" aria-label="Bộ lọc mức độ khóa học">
        <div class="academy-filter-group">
            @foreach([''=>'Tất cả','basic'=>'Cơ bản','advanced'=>'Nâng cao','expert'=>'Chuyên gia'] as $key => $label)
                @php
                    $difficultyIcon = match($key) {
                        '' => 'grid',
                        'basic' => 'book',
                        'advanced' => 'target',
                        'expert' => 'trophy',
                    };
                    $difficultyColor = match($key) {
                        'advanced' => '#B7791F',
                        'expert' => '#B42318',
                        default => '#1F77BE',
                    };
                @endphp
                <button type="button" wire:click="$set('difficulty','{{ $key }}')" class="{{ $difficulty === $key ? 'is-active' : '' }}"><x-icon :name="$difficultyIcon" size="14" :color="$difficultyColor" />{{ $label }}</button>
            @endforeach
        </div>
    </div>

    <div wire:loading.flex wire:target="pillar,difficulty,setPillar" class="academy-loading" role="status" aria-live="polite">
        <x-icon name="refresh" size="15" /> Đang cập nhật danh sách khóa học...
    </div>

    <div class="academy-grid">
        @forelse($courses as $course)
            @php
                $hasAccess = $course->user_enrolled || (auth()->check() && auth()->user()->hasPremiumMembership());
                $difficultyLabel = match($course->difficulty) { 'advanced' => 'Nâng cao', 'expert' => 'Chuyên gia', default => 'Cơ bản' };
            @endphp
            <article class="academy-card">
                <a href="{{ community_route('academy.show', ['id' => $course->id]) }}" class="academy-thumb" aria-label="Xem {{ $course->title }}">
                    @if($course->thumbnail)
                        <img src="{{ asset('storage/'.$course->thumbnail) }}" alt="">
                    @else
                        <span class="academy-thumb-fallback">DSCONS</span>
                    @endif
                    @if($course->is_featured)<span class="academy-featured">Nổi bật</span>@endif
                </a>
                <div class="academy-card-body">
                <div class="academy-badges">
                        <span class="academy-badge pillar"><x-icon name="{{ $pillars[$course->pillar]['icon'] ?? 'layers' }}" size="14" />{{ $pillars[$course->pillar]['name'] ?? ucfirst($course->pillar) }}</span>
                        <span class="academy-badge difficulty-{{ $course->difficulty }}"><x-icon name="target" size="14" :color="match($course->difficulty) { 'advanced' => '#B7791F', 'expert' => '#B42318', default => '#1F77BE' }" />{{ $difficultyLabel }}</span>
                        @if($course->min_level > 1)<span class="academy-badge"><x-icon name="flag" size="14" />Lv.{{ $course->min_level }}+</span>@endif
                    </div>
                    <h2>{{ $course->title }}</h2>
                    <p class="academy-description">{{ Str::limit($course->description ?: 'Một chương trình học có cấu trúc rõ ràng, tập trung vào năng lực có thể áp dụng ngay.', 112) }}</p>
                    <div class="academy-stats">
                        <div class="academy-stat"><strong><x-icon name="book" size="15" />{{ $course->modules_count }}</strong><span>Module</span></div>
                        <div class="academy-stat"><strong><x-icon name="clipboard" size="15" />{{ $course->lesson_count }}</strong><span>Bài học</span></div>
                        <div class="academy-stat"><strong><x-icon name="users" size="15" />{{ number_format($course->enrollments_count) }}</strong><span>Học viên</span></div>
                    </div>
                    @if($hasAccess && $course->lesson_count > 0)
                        <div class="academy-progress">
                            <div class="academy-progress-head"><span><x-icon name="chart" size="14" />{{ $course->completed_lessons }}/{{ $course->lesson_count }} bài đã học</span><strong>{{ $course->progress_percent }}%</strong></div>
                            <div class="academy-progress-track"><div class="academy-progress-fill" style="width:{{ $course->progress_percent }}%"></div></div>
                        </div>
                    @endif
                    <div class="academy-card-footer">
                        <span class="academy-price {{ $course->price <= 0 ? 'is-free' : '' }}"><x-icon name="tag" size="15" />{{ $course->price > 0 ? number_format($course->price, 0, ',', '.').'đ' : 'Miễn phí' }}</span>
                        <a href="{{ community_route('academy.show', ['id' => $course->id]) }}" class="academy-cta">{{ $hasAccess ? 'Tiếp tục học' : 'Xem khóa học' }} <x-icon name="arrow-right" size="15" /></a>
                    </div>
                </div>
            </article>
        @empty
            <div class="academy-empty"><strong>Chưa có khóa học phù hợp</strong><span>Thử thay đổi bộ lọc để xem thêm chương trình.</span></div>
        @endforelse
    </div>
</div>
