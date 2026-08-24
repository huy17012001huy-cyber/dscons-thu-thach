<div class="events-page">
    <style>
        .events-hero { background:#125A96; color:#fff; border-radius:18px; padding:1.35rem 1.5rem; margin-bottom:1rem; position:relative; overflow:hidden; }
        .events-hero::after { content:''; position:absolute; width:210px; height:210px; border:1px solid rgba(255,255,255,.2); border-radius:50%; right:-55px; top:-75px; box-shadow:0 0 0 22px rgba(255,255,255,.05),0 0 0 44px rgba(255,255,255,.04); }
        .events-hero h1 { font-size:clamp(1.35rem,2.5vw,1.9rem); font-weight:800; letter-spacing:-.03em; margin:0; }
        .events-hero p { color:rgba(255,255,255,.78); max-width:620px; margin:.4rem 0 0; font-size:.88rem; line-height:1.6; }
        .events-tabs { display:flex; gap:.35rem; border-bottom:1px solid var(--border); margin-bottom:1rem; overflow-x:auto; }
        .events-tab { min-height:42px; border:0; border-bottom:2px solid transparent; border-radius:10px 10px 0 0; background:transparent; padding:.65rem .8rem; color:var(--text-muted); font-size:.84rem; font-weight:700; cursor:pointer; white-space:nowrap; transition:color .16s ease, background-color .16s ease, border-color .16s ease; }
        .events-tab:hover { background:#EEF7F9; color:#125A96; }
        .events-tab:focus-visible { outline:3px solid rgba(31,119,190,.20); outline-offset:1px; }
        .events-tab.active { color:var(--green); border-bottom-color:var(--green); }
        .events-filters { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.6rem; margin-bottom:1rem; }
        .event-card { display:grid; grid-template-columns:8px minmax(0,1fr) auto; gap:1rem; align-items:stretch; }
        .event-card-rail { border-radius:999px; background:#1f77be; }
        .event-card-rail.offline { background:#f39402; }
        .event-card-body { min-width:0; }
        .event-card-head { display:flex; align-items:flex-start; justify-content:space-between; gap:.7rem; }
        .event-card h2 { color:var(--text); font-size:1.05rem; font-weight:800; line-height:1.3; margin:.5rem 0 .35rem; }
        .event-meta { display:flex; flex-wrap:wrap; gap:.5rem 1rem; color:var(--text-muted); font-size:.78rem; line-height:1.5; }
        .event-meta strong { color:var(--text); font-weight:700; }
        .event-description { color:var(--text-secondary); font-size:.86rem; line-height:1.65; margin:.7rem 0; white-space:pre-line; }
        .event-actions { display:flex; flex-direction:column; align-items:flex-end; justify-content:center; gap:.5rem; min-width:145px; }
        .event-actions .btn { justify-content:center; width:100%; white-space:nowrap; }
        .event-related { color:var(--green); font-size:.78rem; font-weight:700; text-decoration:none; }
        .event-related:hover { text-decoration:underline; }
        .event-status-cancelled { background:#fee2e2;color:#991b1b;border:1px solid #fecaca; }
        .event-status-completed { background:#f1f5f9;color:#475569;border:1px solid #cbd5e1; }
        .event-status-draft { background:#fef3c7;color:#92400e;border:1px solid #fde68a; }
        .events-loading-state { display:flex; align-items:center; gap:8px; min-height:42px; margin:-.35rem 0 1rem; padding:10px 12px; border:1px solid #D7E5EA; border-radius:12px; background:#F8FCFD; color:#61798A; font-size:.78rem; }
        @media (max-width: 760px) {
            .events-hero { padding:1.1rem; border-radius:14px; }
            .events-filters { display:flex; overflow-x:auto; padding-bottom:2px; }
            .events-filters .input { min-width:155px; }
            .event-card { grid-template-columns:5px minmax(0,1fr); gap:.75rem; }
            .event-actions { grid-column:2; align-items:stretch; min-width:0; display:grid; grid-template-columns:1fr 1fr; }
            .event-actions .btn { width:auto; }
            .event-card-head { display:block; }
        }
        @media (max-width:420px) { .event-actions { grid-template-columns:1fr; } }
    </style>

    <section class="events-hero">
        <h1>Sự kiện</h1>
        <p>Các workshop, livestream và buổi mentor dành cho thành viên DSCons. Bạn có thể xem lịch công khai và đăng ký khi đã thuộc khóa học hoặc Challenge tương ứng.</p>
    </section>

    <div class="events-tabs" role="tablist" aria-label="Bộ lọc thời gian sự kiện">
        <button type="button" role="tab" aria-selected="{{ $tab === 'upcoming' ? 'true' : 'false' }}" class="events-tab {{ $tab === 'upcoming' ? 'active' : '' }}" wire:click="$set('tab', 'upcoming')">Sắp tới</button>
        <button type="button" role="tab" aria-selected="{{ $tab === 'past' ? 'true' : 'false' }}" class="events-tab {{ $tab === 'past' ? 'active' : '' }}" wire:click="$set('tab', 'past')">Đã kết thúc</button>
    </div>

    <div class="events-filters" aria-label="Bộ lọc sự kiện">
        <select wire:model.live="courseFilter" class="input" aria-label="Lọc theo khóa học">
            <option value="">Tất cả khóa học</option>
            @foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->title }}</option>@endforeach
        </select>
        <select wire:model.live="challengeFilter" class="input" aria-label="Lọc theo Challenge">
            <option value="">Tất cả Challenge</option>
            @foreach($challenges as $challenge)<option value="{{ $challenge->id }}">{{ $challenge->title }}</option>@endforeach
        </select>
        <select wire:model.live="typeFilter" class="input" aria-label="Lọc theo loại sự kiện">
            <option value="">Tất cả loại</option>
            @foreach($typeLabels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
        </select>
        <select wire:model.live="formatFilter" class="input" aria-label="Lọc theo hình thức">
            <option value="">Online & Offline</option>
            <option value="online">Online</option>
            <option value="offline">Offline</option>
        </select>
    </div>

    <div wire:loading.flex class="events-loading-state" role="status" aria-live="polite">
        <span class="loading-dot" aria-hidden="true"></span> Đang cập nhật lịch sự kiện...
    </div>

    @if($events->isEmpty())
        <div class="card empty-state">
            <div class="empty-icon" aria-hidden="true">◷</div>
            <strong>{{ $tab === 'past' ? 'Chưa có sự kiện đã kết thúc' : 'Chưa có sự kiện sắp tới' }}</strong>
            <p>{{ $tab === 'past' ? 'Lịch sử các buổi học và meetup sẽ được lưu tại đây.' : 'Khi Admin công bố lịch mới, thông tin sẽ xuất hiện ở đây.' }}</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($events as $event)
                @php
                    $registration = $registrations->get($event->id);
                    $isRegistered = $registration?->status === 'registered';
                    $isEligible = $eligible[$event->id] ?? false;
                    $isFull = $event->capacity !== null && $event->registered_count >= $event->capacity;
                    $canRsvp = !$isAdmin && $isEligible && $event->status === 'published' && $event->ends_at->isFuture() && (!$isFull || $isRegistered);
                    $canJoin = ($isAdmin && $event->status === 'published' && $event->ends_at->isFuture() && filled($event->meeting_url))
                        || ($isEligible && $isRegistered && $event->status === 'published' && $event->starts_at->isPast() && $event->ends_at->isFuture() && filled($event->meeting_url));
                    $related = $event->course ?: $event->expedition;
                @endphp
                <article class="card event-card">
                    <div class="event-card-rail {{ $event->format === 'offline' ? 'offline' : '' }}" aria-hidden="true"></div>
                    <div class="event-card-body">
                        <div class="event-card-head">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="badge" style="background:#e1f4f7;color:#125a96;">{{ $event->type_label }}</span>
                                <span class="badge" style="background:{{ $event->format === 'online' ? '#e8f5ff' : '#fff1d6' }};color:{{ $event->format === 'online' ? '#125a96' : '#995600' }};">{{ $event->format === 'online' ? 'Online' : 'Offline' }}</span>
                                @if($event->status !== 'published')
                                    <span class="badge event-status-{{ $event->status }}">{{ $event->status_label }}</span>
                                @endif
                            </div>
                        </div>
                        <h2>{{ $event->title }}</h2>
                        @if($related)
                            <div class="mb-2">
                                @if($event->course)
                                    <a class="event-related" href="{{ request()->routeIs('community.*') ? community_route('academy.show', ['id' => $event->course->id]) : route('academy.show', $event->course->id) }}">Khóa học · {{ $event->course->title }}</a>
                                @else
                                    <a class="event-related" href="{{ request()->routeIs('community.*') ? community_route('challenge.show', ['slug' => $event->expedition->slug]) : route('challenge.show', $event->expedition->slug) }}">Challenge · {{ $event->expedition->title }}</a>
                                @endif
                            </div>
                        @endif
                        <div class="event-meta">
                            <span>▣ <strong>{{ $event->starts_at->timezone('Asia/Ho_Chi_Minh')->translatedFormat('D, d/m/Y · H:i') }}</strong></span>
                            <span>Thời lượng <strong>{{ $event->duration_minutes >= 60 ? floor($event->duration_minutes / 60).' giờ '.($event->duration_minutes % 60 ? $event->duration_minutes % 60 .' phút' : '') : $event->duration_minutes.' phút' }}</strong></span>
                            @if($event->capacity)<span>Đã đăng ký <strong>{{ $event->registered_count }}/{{ $event->capacity }}</strong></span>@else<span><strong>{{ $event->registered_count }}</strong> người đã đăng ký</span>@endif
                        </div>
                        @if($event->description)<p class="event-description">{{ Str::limit($event->description, 260) }}</p>@endif
                        @if($event->format === 'offline' && $event->location && $event->status === 'published')
                            <p class="text-sm text-slate-600">Địa điểm: <strong>{{ $event->location }}</strong></p>
                        @endif
                    </div>
                    <div class="event-actions">
                        @if($canJoin)
                            <a href="{{ $event->meeting_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-gold">Tham gia meeting ↗</a>
                        @elseif($event->status === 'cancelled')
                            <span class="badge event-status-cancelled" style="justify-content:center;padding:.55rem .65rem;">Sự kiện đã hủy</span>
                        @elseif($isRegistered && ($tab === 'past' || $event->status === 'completed'))
                            <span class="badge event-status-completed" style="justify-content:center;padding:.55rem .65rem;">Sự kiện đã kết thúc</span>
                        @elseif($isRegistered)
                            <span class="badge" style="background:#d1fae5;color:#065f46;justify-content:center;padding:.55rem .65rem;">✓ Đã đăng ký</span>
                            @if($tab === 'upcoming' && $event->status === 'published')
                                <button type="button" class="btn btn-ghost" wire:click="cancelRegistration({{ $event->id }})">Hủy đăng ký</button>
                            @endif
                        @elseif($canRsvp)
                            <button type="button" class="btn btn-primary" wire:click="registerEvent({{ $event->id }})" wire:loading.attr="disabled" wire:target="registerEvent({{ $event->id }})"><span wire:loading.remove wire:target="registerEvent({{ $event->id }})">Đăng ký tham gia</span><span wire:loading wire:target="registerEvent({{ $event->id }})">Đang đăng ký...</span></button>
                        @elseif($isFull)
                            <span class="badge" style="background:#f1f5f9;color:#475569;justify-content:center;padding:.55rem .65rem;">Đã đủ chỗ</span>
                        @elseif($event->status === 'cancelled')
                            <span class="badge event-status-cancelled" style="justify-content:center;padding:.55rem .65rem;">Sự kiện đã hủy</span>
                        @elseif($event->status === 'completed' || $tab === 'past')
                            <span class="badge event-status-completed" style="justify-content:center;padding:.55rem .65rem;">Sự kiện đã kết thúc</span>
                        @elseif(!$isEligible)
                            <span class="text-xs text-slate-500 text-right">Tham gia khóa học/Challenge để đăng ký</span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
