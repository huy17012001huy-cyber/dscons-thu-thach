<div class="admin-events">
    <style>
        .admin-events-toolbar { display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1rem; }
        .admin-events-toolbar h1 { color:var(--text);font-size:clamp(1.35rem,2.5vw,1.75rem);font-weight:800;letter-spacing:-.03em;margin:0; }
        .admin-events-toolbar p { color:var(--text-muted);font-size:.84rem;margin-top:.25rem; }
        .admin-events-table { width:100%;border-collapse:collapse; }
        .admin-events-table th { color:var(--text-muted);font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;text-align:left;padding:.65rem .75rem;border-bottom:1px solid var(--border); }
        .admin-events-table td { color:var(--text-secondary);font-size:.82rem;padding:.8rem .75rem;border-bottom:1px solid #edf2f5;vertical-align:top; }
        .admin-events-table tr:last-child td { border-bottom:0; }
        .admin-event-title { color:var(--text);font-weight:700;line-height:1.4; }
        .admin-event-target { color:var(--text-muted);font-size:.74rem;margin-top:.2rem; }
        .admin-actions { display:flex;flex-wrap:wrap;gap:.35rem; }
        .admin-actions .btn { min-height:34px;padding:.35rem .6rem;font-size:.74rem; }
        .admin-modal-backdrop { position:fixed;inset:0;background:rgba(16,42,59,.45);z-index:1000;display:flex;align-items:center;justify-content:center;padding:1rem; }
        .admin-modal { width:min(720px,100%);max-height:92vh;overflow:auto;background:#fff;border:1px solid var(--border);border-radius:16px;box-shadow:0 24px 70px rgba(18,59,89,.2);padding:1.25rem; }
        .admin-form-grid { display:grid;grid-template-columns:1fr 1fr;gap:.75rem; }
        .admin-form-grid .full { grid-column:1/-1; }
        .admin-label { display:block;color:var(--text-secondary);font-size:.76rem;font-weight:700;margin-bottom:.3rem; }
        .admin-error { color:#b91c1c;font-size:.72rem;margin-top:.25rem; }
        @media (max-width:760px) { .admin-events-toolbar{display:block}.admin-events-toolbar .btn{margin-top:.75rem}.admin-table-scroll{overflow-x:auto}.admin-events-table{min-width:720px}.admin-form-grid{grid-template-columns:1fr}.admin-form-grid .full{grid-column:auto} }
    </style>

    <div class="admin-events-toolbar">
        <div><h1>Quản lý sự kiện</h1><p>Tạo lịch workshop, livestream và mentor meeting theo từng khóa học hoặc Challenge.</p></div>
        <button type="button" class="btn btn-primary" wire:click="openCreate">+ Tạo sự kiện</button>
    </div>

    <div class="card admin-table-scroll">
        @if($events->isEmpty())
            <div class="empty-state"><div class="empty-icon" aria-hidden="true">◷</div><strong>Chưa có sự kiện</strong><p>Tạo sự kiện đầu tiên để học viên nhìn thấy lịch học.</p></div>
        @else
            <table class="admin-events-table">
                <thead><tr><th>Sự kiện</th><th>Lịch</th><th>Trạng thái</th><th>Đăng ký</th><th>Thao tác</th></tr></thead>
                <tbody>
                @foreach($events as $event)
                    <tr>
                        <td><div class="admin-event-title">{{ $event->title }}</div><div class="admin-event-target">{{ $event->course?->title ? 'Khóa học · '.$event->course->title : 'Challenge · '.$event->expedition?->title }}</div></td>
                        <td>{{ $event->starts_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}<br><span class="text-xs text-slate-500">đến {{ $event->ends_at->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y') }}</span></td>
                        <td><span class="badge {{ $event->status === 'published' ? 'bg-emerald-100 text-emerald-700' : ($event->status === 'cancelled' ? 'event-status-cancelled' : 'event-status-draft') }}">{{ $event->status_label }}</span></td>
                        <td><button type="button" class="event-related" wire:click="$set('selectedEventId', {{ $event->id }})">{{ $event->registered_count }}{{ $event->capacity ? '/'.$event->capacity : '' }} người</button></td>
                        <td><div class="admin-actions"><button type="button" class="btn btn-secondary" wire:click="openEdit({{ $event->id }})">Sửa</button>@if($event->status === 'draft')<button type="button" class="btn btn-primary" wire:click="publishEvent({{ $event->id }})">Publish</button>@elseif($event->status === 'published')<button type="button" class="btn btn-secondary" wire:click="cancelEvent({{ $event->id }})">Hủy</button><button type="button" class="btn btn-secondary" wire:click="completeEvent({{ $event->id }})">Hoàn thành</button>@endif @if(in_array($event->status,['draft','cancelled'],true))<button type="button" class="btn btn-danger" wire:click="deleteEvent({{ $event->id }})" wire:confirm="Xóa sự kiện này?">Xóa</button>@endif</div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if($selectedEventId && $selectedEvent = $events->firstWhere('id', $selectedEventId))
        <div class="card mt-4">
            <div class="flex items-center justify-between gap-3 mb-3"><div><h2 class="font-bold text-slate-900">Danh sách đăng ký</h2><p class="text-xs text-slate-500">{{ $selectedEvent->title }}</p></div><button type="button" class="btn btn-secondary" wire:click="exportCsv({{ $selectedEvent->id }})">Xuất CSV</button></div>
            @if($registrations->isEmpty())<p class="text-sm text-slate-500">Chưa có người đăng ký.</p>@else
                <div class="space-y-2">@foreach($registrations as $registration)<div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-2"><div><p class="text-sm font-semibold text-slate-900">{{ $registration->user?->name }}</p><p class="text-xs text-slate-500">{{ $registration->user?->email }} · {{ $registration->registered_at?->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</p></div><button type="button" class="btn {{ $registration->attended_at ? 'btn-success' : 'btn-secondary' }}" wire:click="markAttendance({{ $registration->id }})">{{ $registration->attended_at ? '✓ Đã tham dự' : 'Đánh dấu tham dự' }}</button></div>@endforeach</div>
            @endif
        </div>
    @endif

    @if($showForm)
        <div class="admin-modal-backdrop" role="dialog" aria-modal="true" aria-label="Tạo sự kiện" wire:keydown.escape="$set('showForm', false)">
            <div class="admin-modal">
                <div class="flex items-center justify-between gap-3 mb-4"><h2 class="text-lg font-extrabold text-slate-900">{{ $editingEventId ? 'Sửa sự kiện' : 'Tạo sự kiện mới' }}</h2><button type="button" class="panel-btn" wire:click="$set('showForm', false)" aria-label="Đóng">×</button></div>
                <form wire:submit="saveEvent" class="admin-form-grid">
                    <div class="full"><label class="admin-label">Tên sự kiện *</label><input wire:model="title" class="input" placeholder="VD: Workshop triển khai BIM thực chiến">@error('title')<p class="admin-error">{{ $message }}</p>@enderror</div>
                    <div><label class="admin-label">Gắn với khóa học</label><select wire:model="courseId" class="input"><option value="">— Không chọn —</option>@foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->title }}</option>@endforeach</select></div>
                    <div><label class="admin-label">Hoặc gắn với Challenge</label><select wire:model="expeditionId" class="input"><option value="">— Không chọn —</option>@foreach($challenges as $challenge)<option value="{{ $challenge->id }}">{{ $challenge->title }}</option>@endforeach</select></div>
                    @error('courseId')<p class="admin-error full">{{ $message }}</p>@enderror
                    <div><label class="admin-label">Loại sự kiện *</label><select wire:model="eventType" class="input">@foreach($typeLabels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
                    <div><label class="admin-label">Hình thức *</label><select wire:model="format" class="input"><option value="online">Online</option><option value="offline">Offline</option></select></div>
                    <div><label class="admin-label">Bắt đầu (giờ Việt Nam) *</label><input wire:model="startsAt" type="datetime-local" class="input">@error('startsAt')<p class="admin-error">{{ $message }}</p>@enderror</div>
                    <div><label class="admin-label">Kết thúc (giờ Việt Nam) *</label><input wire:model="endsAt" type="datetime-local" class="input">@error('endsAt')<p class="admin-error">{{ $message }}</p>@enderror</div>
                    <div><label class="admin-label">Link meeting (online)</label><input wire:model="meetingUrl" type="url" class="input" placeholder="https://meet.google.com/...">@error('meetingUrl')<p class="admin-error">{{ $message }}</p>@enderror</div>
                    <div><label class="admin-label">Địa điểm (offline)</label><input wire:model="location" class="input" placeholder="VD: Văn phòng DSCons">@error('location')<p class="admin-error">{{ $message }}</p>@enderror</div>
                    <div><label class="admin-label">Sức chứa (để trống = không giới hạn)</label><input wire:model="capacity" type="number" min="1" class="input" placeholder="Không giới hạn">@error('capacity')<p class="admin-error">{{ $message }}</p>@enderror</div>
                    <div><label class="admin-label">Trạng thái</label><select wire:model="status" class="input"><option value="draft">Bản nháp</option><option value="published">Publish ngay</option><option value="cancelled">Đã hủy</option><option value="completed">Đã hoàn thành</option></select></div>
                    <div class="full"><label class="admin-label">Mô tả ngắn</label><textarea wire:model="description" class="input" rows="4" placeholder="Nội dung, mục tiêu và thông tin cần chuẩn bị..."></textarea></div>
                    <div class="full flex justify-end gap-2 pt-2"><button type="button" class="btn btn-secondary" wire:click="$set('showForm', false)">Hủy</button><button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Lưu sự kiện</button></div>
                </form>
            </div>
        </div>
    @endif
</div>
