<div class="admin-events">
    <style>
        .admin-events { max-width: 1200px; margin: 0 auto; padding: 30px clamp(16px, 3vw, 30px) 64px; color: var(--text); }
        .admin-events-header { display: flex; align-items: end; justify-content: space-between; gap: 18px; margin-bottom: 22px; }
        .admin-events-kicker { display: flex; align-items: center; gap: 7px; margin: 0 0 8px; color: var(--green); font-size: 11px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
        .admin-events-kicker svg { width: 15px; height: 15px; }
        .admin-events-header h1 { margin: 0; color: var(--text); font-size: clamp(26px, 3vw, 32px); font-weight: 800; letter-spacing: -.04em; line-height: 1.15; }
        .admin-events-header p { max-width: 650px; margin: 8px 0 0; color: var(--text-muted); font-size: 14px; line-height: 1.65; }
        .admin-events-primary { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; flex: 0 0 auto; text-decoration: none; }
        .admin-events-primary svg { width: 17px; height: 17px; }
        .admin-events-card { overflow: hidden; border: 1px solid var(--border); border-radius: 18px; background: #fff; box-shadow: 0 2px 8px rgba(18,59,89,.035); }
        .admin-events-table-scroll { overflow-x: auto; }
        .admin-events-table { width: 100%; min-width: 760px; border-collapse: collapse; }
        .admin-events-table th { padding: 13px 16px; border-bottom: 1px solid var(--border); color: var(--text-muted); font-size: 11px; font-weight: 800; letter-spacing: .07em; text-align: left; text-transform: uppercase; white-space: nowrap; }
        .admin-events-table td { padding: 16px; border-bottom: 1px solid #E7EEF2; color: var(--text-secondary); font-size: 13px; line-height: 1.5; vertical-align: top; }
        .admin-events-table tr:last-child td { border-bottom: 0; }
        .admin-event-title { color: var(--text); font-size: 14px; font-weight: 750; line-height: 1.45; }
        .admin-event-target { margin-top: 3px; color: var(--text-muted); font-size: 12px; }
        .admin-event-schedule { color: var(--text); font-weight: 650; white-space: nowrap; }
        .admin-event-schedule span { display: block; margin-top: 2px; color: var(--text-muted); font-size: 11px; font-weight: 500; }
        .admin-event-status { display: inline-flex; align-items: center; min-height: 25px; padding: 4px 8px; border: 1px solid transparent; border-radius: 999px; font-size: 11px; font-weight: 800; white-space: nowrap; }
        .admin-event-status.status-published { border-color: #B7DFC9; background: #EAF7EF; color: #18794E; }
        .admin-event-status.status-draft { border-color: #C8DDE7; background: #F1F8FB; color: #125A96; }
        .admin-event-status.status-cancelled { border-color: #F0C6C1; background: #FFF0EE; color: #B42318; }
        .admin-event-status.status-completed { border-color: #D9DEE2; background: #F5F7F8; color: #516472; }
        .admin-event-rsvp { min-height: 32px; padding: 4px 8px; border: 0; border-radius: 8px; background: transparent; color: var(--green); font: inherit; font-size: 12px; font-weight: 750; text-decoration: underline; text-decoration-color: #B9D4E3; text-underline-offset: 3px; cursor: pointer; white-space: nowrap; }
        .admin-event-rsvp:hover { color: var(--green-dark, #125A96); }
        .admin-event-rsvp:focus-visible { outline: 3px solid rgba(31,119,190,.2); outline-offset: 2px; }
        .admin-event-actions { display: flex; flex-wrap: wrap; gap: 7px; min-width: 185px; }
        .admin-event-actions .btn { min-height: 34px; padding: 6px 10px; font-size: 12px; }
        .admin-events-empty { display: grid; justify-items: center; gap: 8px; padding: 54px 24px; color: var(--text-muted); text-align: center; }
        .admin-events-empty svg { width: 34px; height: 34px; color: var(--green); }
        .admin-events-empty strong { color: var(--text); font-size: 16px; }
        .admin-events-empty p { max-width: 410px; margin: 0; font-size: 13px; line-height: 1.6; }
        .admin-events-registrations { margin-top: 16px; padding: 20px 22px; }
        .admin-events-registrations-header { display: flex; align-items: start; justify-content: space-between; gap: 14px; margin-bottom: 12px; }
        .admin-events-registrations h2 { margin: 0; color: var(--text); font-size: 18px; letter-spacing: -.02em; }
        .admin-events-registrations-header p { margin: 4px 0 0; color: var(--text-muted); font-size: 12px; }
        .admin-events-registration-list { display: grid; }
        .admin-events-registration { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 13px 0; border-top: 1px solid #E7EEF2; }
        .admin-events-registration strong { display: block; color: var(--text); font-size: 14px; }
        .admin-events-registration span { display: block; margin-top: 3px; color: var(--text-muted); font-size: 12px; }
        .admin-events-registration .btn { min-height: 36px; flex: 0 0 auto; padding: 6px 10px; font-size: 12px; }
        .admin-events-registration-empty { margin: 0; padding-top: 13px; border-top: 1px solid #E7EEF2; color: var(--text-muted); font-size: 13px; }
        .admin-events-modal-backdrop { position: fixed; z-index: 1000; inset: 0; display: flex; align-items: center; justify-content: center; padding: 20px; background: rgba(16,42,59,.52); }
        .admin-events-modal { width: min(760px, 100%); max-height: min(760px, calc(100vh - 40px)); overflow: auto; border: 1px solid var(--border); border-radius: 18px; background: #fff; box-shadow: 0 24px 70px rgba(18,59,89,.24); }
        .admin-events-modal-header { display: flex; align-items: start; justify-content: space-between; gap: 16px; padding: 20px 22px; border-bottom: 1px solid var(--border); }
        .admin-events-modal-header h2 { margin: 0; color: var(--text); font-size: 20px; letter-spacing: -.02em; }
        .admin-events-modal-header p { margin: 5px 0 0; color: var(--text-muted); font-size: 12px; }
        .admin-events-close { display: grid; width: 40px; height: 40px; flex: 0 0 auto; place-items: center; border: 1px solid var(--border); border-radius: 10px; background: #fff; color: var(--text); cursor: pointer; transition: border-color .15s ease, background .15s ease; }
        .admin-events-close:hover { border-color: #A9CAD7; background: #F3F9FB; }
        .admin-events-close:focus-visible { outline: 3px solid rgba(31,119,190,.2); outline-offset: 2px; }
        .admin-events-close svg { width: 18px; height: 18px; }
        .admin-events-form { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; padding: 22px; }
        .admin-events-form .full { grid-column: 1 / -1; }
        .admin-events-field { display: grid; gap: 7px; color: var(--text); font-size: 12px; font-weight: 750; }
        .admin-events-field .input { width: 100%; min-height: 43px; box-sizing: border-box; border-color: var(--border); border-radius: 10px; color: var(--text); font: inherit; font-size: 14px; }
        .admin-events-field textarea.input { min-height: 112px; padding-top: 10px; resize: vertical; }
        .admin-events-field .input:focus-visible { border-color: var(--green); box-shadow: 0 0 0 3px rgba(31,119,190,.14); outline: none; }
        .admin-events-error { margin: 0; color: var(--ds-danger); font-size: 12px; font-weight: 500; line-height: 1.45; }
        .admin-events-form-actions { display: flex; justify-content: flex-end; gap: 10px; padding-top: 2px; }
        .admin-events-form-actions .btn { min-height: 42px; min-width: 96px; }
        @media (max-width: 720px) { .admin-events { padding: 20px 14px 44px; } .admin-events-header { display: block; } .admin-events-primary { width: 100%; margin-top: 16px; } .admin-events-table th, .admin-events-table td { padding-left: 14px; padding-right: 14px; } .admin-events-registrations, .admin-events-modal-header, .admin-events-form { padding-left: 16px; padding-right: 16px; } .admin-events-form { grid-template-columns: 1fr; } .admin-events-form .full { grid-column: auto; } .admin-events-registration { align-items: start; flex-direction: column; } .admin-events-registration .btn { width: 100%; } .admin-events-modal-backdrop { align-items: end; padding: 0; } .admin-events-modal { max-height: 92vh; border-bottom-right-radius: 0; border-bottom-left-radius: 0; } .admin-events-form-actions .btn { flex: 1; } }
    </style>

    <header class="admin-events-header">
        <div>
            <div class="admin-events-kicker"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>Quản trị học tập</div>
            <h1>Quản lý sự kiện</h1>
            <p>Tạo và theo dõi workshop, livestream hoặc buổi gặp mentor cho từng khóa học hay Challenge.</p>
        </div>
        <button type="button" class="btn btn-primary admin-events-primary" wire:click="openCreate"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Tạo sự kiện</button>
    </header>

    <section class="admin-events-card admin-events-table-scroll" aria-label="Danh sách sự kiện">
        @if($events->isEmpty())
            <div class="admin-events-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18M8 15h3"/></svg>
                <strong>Chưa có sự kiện</strong>
                <p>Tạo sự kiện đầu tiên để học viên nhìn thấy lịch học và đăng ký tham gia.</p>
            </div>
        @else
            <table class="admin-events-table">
                <thead><tr><th>Sự kiện</th><th>Lịch</th><th>Trạng thái</th><th>Đăng ký</th><th>Thao tác</th></tr></thead>
                <tbody>
                @foreach($events as $event)
                    <tr>
                        <td><div class="admin-event-title">{{ $event->title }}</div><div class="admin-event-target">{{ $event->course?->title ? 'Khóa học · '.$event->course->title : 'Challenge · '.$event->expedition?->title }}</div></td>
                        <td><div class="admin-event-schedule">{{ $event->starts_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}<span>đến {{ $event->ends_at->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y') }}</span></div></td>
                        <td><span class="admin-event-status status-{{ $event->status }}">{{ $event->status_label }}</span></td>
                        <td><button type="button" class="admin-event-rsvp" wire:click="$set('selectedEventId', {{ $event->id }})" aria-label="Xem danh sách đăng ký sự kiện {{ $event->title }}">{{ $event->registered_count }}{{ $event->capacity ? '/'.$event->capacity : '' }} người</button></td>
                        <td>
                            <div class="admin-event-actions">
                                <button type="button" class="btn btn-secondary" wire:click="openEdit({{ $event->id }})">Sửa</button>
                                @if($event->status === 'draft')
                                    <button type="button" class="btn btn-primary" wire:click="publishEvent({{ $event->id }})">Công bố</button>
                                @elseif($event->status === 'published')
                                    <button type="button" class="btn btn-secondary" wire:click="cancelEvent({{ $event->id }})">Hủy</button>
                                    <button type="button" class="btn btn-secondary" wire:click="completeEvent({{ $event->id }})">Hoàn thành</button>
                                @endif
                                @if(in_array($event->status,['draft','cancelled'],true))
                                    <button type="button" class="btn btn-danger" wire:click="deleteEvent({{ $event->id }})" wire:confirm="Xóa sự kiện này?">Xóa</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </section>

    @if($selectedEventId && $selectedEvent = $events->firstWhere('id', $selectedEventId))
        <section class="admin-events-card admin-events-registrations" aria-labelledby="registration-heading">
            <div class="admin-events-registrations-header">
                <div><h2 id="registration-heading">Danh sách đăng ký</h2><p>{{ $selectedEvent->title }}</p></div>
                <button type="button" class="btn btn-secondary" wire:click="exportCsv({{ $selectedEvent->id }})">Xuất CSV</button>
            </div>
            @if($registrations->isEmpty())
                <p class="admin-events-registration-empty">Chưa có người đăng ký.</p>
            @else
                <div class="admin-events-registration-list">
                    @foreach($registrations as $registration)
                        <div class="admin-events-registration">
                            <div><strong>{{ $registration->user?->name }}</strong><span>{{ $registration->user?->email }} · {{ $registration->registered_at?->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</span></div>
                            <button type="button" class="btn {{ $registration->attended_at ? 'btn-success' : 'btn-secondary' }}" wire:click="markAttendance({{ $registration->id }})">{{ $registration->attended_at ? 'Đã tham dự' : 'Đánh dấu tham dự' }}</button>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    @if($showForm)
        <div class="admin-events-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="event-dialog-title" wire:keydown.escape="$set('showForm', false)" wire:click.self="$set('showForm', false)" x-data x-init="$nextTick(() => $refs.eventTitle.focus())">
            <div class="admin-events-modal">
                <div class="admin-events-modal-header">
                    <div><h2 id="event-dialog-title">{{ $editingEventId ? 'Sửa sự kiện' : 'Tạo sự kiện mới' }}</h2><p>Chỉ học viên đủ điều kiện mới có thể đăng ký và nhận link tham gia.</p></div>
                    <button type="button" class="admin-events-close" wire:click="$set('showForm', false)" aria-label="Đóng hộp tạo sự kiện"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
                </div>
                <form wire:submit="saveEvent" class="admin-events-form">
                    <label class="admin-events-field full" for="event-title">Tên sự kiện *<input x-ref="eventTitle" id="event-title" wire:model="title" class="input" placeholder="VD: Workshop triển khai BIM thực chiến" aria-describedby="event-title-error" @error('title') aria-invalid="true" @enderror>@error('title')<span id="event-title-error" class="admin-events-error" role="alert">{{ $message }}</span>@enderror</label>
                    <label class="admin-events-field" for="event-course">Gắn với khóa học<select id="event-course" wire:model="courseId" class="input" aria-describedby="event-target-error"><option value="">— Không chọn —</option>@foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->title }}</option>@endforeach</select></label>
                    <label class="admin-events-field" for="event-challenge">Hoặc gắn với Challenge<select id="event-challenge" wire:model="expeditionId" class="input" aria-describedby="event-target-error"><option value="">— Không chọn —</option>@foreach($challenges as $challenge)<option value="{{ $challenge->id }}">{{ $challenge->title }}</option>@endforeach</select></label>
                    @error('courseId')<p id="event-target-error" class="admin-events-error full" role="alert">{{ $message }}</p>@enderror
                    <label class="admin-events-field" for="event-type">Loại sự kiện *<select id="event-type" wire:model="eventType" class="input">@foreach($typeLabels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
                    <label class="admin-events-field" for="event-format">Hình thức *<select id="event-format" wire:model="format" class="input"><option value="online">Trực tuyến</option><option value="offline">Trực tiếp</option></select></label>
                    <label class="admin-events-field" for="event-starts">Bắt đầu (giờ Việt Nam) *<input id="event-starts" wire:model="startsAt" type="datetime-local" class="input" aria-describedby="event-starts-error" @error('startsAt') aria-invalid="true" @enderror>@error('startsAt')<span id="event-starts-error" class="admin-events-error" role="alert">{{ $message }}</span>@enderror</label>
                    <label class="admin-events-field" for="event-ends">Kết thúc (giờ Việt Nam) *<input id="event-ends" wire:model="endsAt" type="datetime-local" class="input" aria-describedby="event-ends-error" @error('endsAt') aria-invalid="true" @enderror>@error('endsAt')<span id="event-ends-error" class="admin-events-error" role="alert">{{ $message }}</span>@enderror</label>
                    <label class="admin-events-field" for="event-meeting-url">Link meeting (trực tuyến)<input id="event-meeting-url" wire:model="meetingUrl" type="url" class="input" placeholder="https://meet.google.com/..." aria-describedby="event-meeting-url-error" @error('meetingUrl') aria-invalid="true" @enderror>@error('meetingUrl')<span id="event-meeting-url-error" class="admin-events-error" role="alert">{{ $message }}</span>@enderror</label>
                    <label class="admin-events-field" for="event-location">Địa điểm (trực tiếp)<input id="event-location" wire:model="location" class="input" placeholder="VD: Văn phòng DSCons" aria-describedby="event-location-error" @error('location') aria-invalid="true" @enderror>@error('location')<span id="event-location-error" class="admin-events-error" role="alert">{{ $message }}</span>@enderror</label>
                    <label class="admin-events-field" for="event-capacity">Sức chứa <span style="color:var(--text-muted);font-size:11px;font-weight:500;">Để trống nếu không giới hạn</span><input id="event-capacity" wire:model="capacity" type="number" min="1" class="input" placeholder="Không giới hạn" aria-describedby="event-capacity-error" @error('capacity') aria-invalid="true" @enderror>@error('capacity')<span id="event-capacity-error" class="admin-events-error" role="alert">{{ $message }}</span>@enderror</label>
                    <label class="admin-events-field" for="event-status">Trạng thái<select id="event-status" wire:model="status" class="input"><option value="draft">Bản nháp</option><option value="published">Công bố ngay</option><option value="cancelled">Đã hủy</option><option value="completed">Đã hoàn thành</option></select></label>
                    <label class="admin-events-field full" for="event-description">Mô tả ngắn<textarea id="event-description" wire:model="description" class="input" rows="4" placeholder="Nội dung, mục tiêu và thông tin cần chuẩn bị..."></textarea></label>
                    <div class="admin-events-form-actions full"><button type="button" class="btn btn-secondary" wire:click="$set('showForm', false)">Hủy</button><button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="saveEvent"><span wire:loading.remove wire:target="saveEvent">Lưu sự kiện</span><span wire:loading wire:target="saveEvent">Đang lưu…</span></button></div>
                </form>
            </div>
        </div>
    @endif
</div>
