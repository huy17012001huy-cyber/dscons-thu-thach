<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\User;
use App\Notifications\EventNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class AdminEvents extends Component
{
    public bool $showForm = false;
    public ?int $editingEventId = null;
    public ?int $selectedEventId = null;

    public string $title = '';
    public string $description = '';
    public string $eventType = 'workshop';
    public string $format = 'online';
    public ?int $courseId = null;
    public ?int $expeditionId = null;
    public string $startsAt = '';
    public string $endsAt = '';
    public string $meetingUrl = '';
    public string $location = '';
    public string $capacity = '';
    public string $status = 'draft';

    public function updatedCourseId($value): void
    {
        if (filled($value)) $this->expeditionId = null;
    }

    public function updatedExpeditionId($value): void
    {
        if (filled($value)) $this->courseId = null;
    }

    public function openCreate(): void
    {
        abort_unless(Auth::user()?->isBrandAdmin(), 403);
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        abort_unless(Auth::user()?->isBrandAdmin(), 403);
        $event = Event::query()->findOrFail($id);
        $this->editingEventId = $event->id;
        $this->title = $event->title;
        $this->description = $event->description ?? '';
        $this->eventType = $event->event_type;
        $this->format = $event->format;
        $this->courseId = $event->course_id;
        $this->expeditionId = $event->expedition_id;
        $this->startsAt = $event->starts_at?->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d\\TH:i') ?? '';
        $this->endsAt = $event->ends_at?->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d\\TH:i') ?? '';
        $this->meetingUrl = $event->meeting_url ?? '';
        $this->location = $event->location ?? '';
        $this->capacity = $event->capacity !== null ? (string) $event->capacity : '';
        $this->status = $event->status;
        $this->showForm = true;
    }

    public function saveEvent(): void
    {
        abort_unless(Auth::user()?->isBrandAdmin(), 403);

        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'eventType' => 'required|in:workshop,live,mentor,offline,other',
            'format' => 'required|in:online,offline',
            'courseId' => 'nullable|integer|exists:courses,id',
            'expeditionId' => 'nullable|integer|exists:expeditions,id',
            'startsAt' => 'required|date',
            'endsAt' => 'required|date',
            'meetingUrl' => 'nullable|url|max:2000',
            'location' => 'nullable|string|max:500',
            'capacity' => 'nullable|integer|min:1|max:1000000',
            'status' => 'required|in:draft,published,cancelled,completed',
        ]);

        if (($this->courseId ? 1 : 0) + ($this->expeditionId ? 1 : 0) !== 1) {
            $this->addError('courseId', 'Hãy chọn đúng một khóa học hoặc Challenge.');
            return;
        }

        $starts = Carbon::parse($this->startsAt, 'Asia/Ho_Chi_Minh')->utc();
        $ends = Carbon::parse($this->endsAt, 'Asia/Ho_Chi_Minh')->utc();
        if ($ends->lessThanOrEqualTo($starts)) {
            $this->addError('endsAt', 'Thời gian kết thúc phải sau thời gian bắt đầu.');
            return;
        }
        if ($this->format === 'online' && $this->meetingUrl === '') {
            $this->addError('meetingUrl', 'Sự kiện online cần có link meeting.');
            return;
        }
        if ($this->format === 'offline' && $this->location === '') {
            $this->addError('location', 'Sự kiện offline cần có địa điểm.');
            return;
        }

        $data = [
            'course_id' => $this->courseId ?: null,
            'expedition_id' => $this->expeditionId ?: null,
            'title' => trim($this->title),
            'description' => trim($this->description) ?: null,
            'event_type' => $this->eventType,
            'format' => $this->format,
            'starts_at' => $starts,
            'ends_at' => $ends,
            'meeting_url' => $this->meetingUrl !== '' ? trim($this->meetingUrl) : null,
            'location' => $this->location !== '' ? trim($this->location) : null,
            'capacity' => $this->capacity !== '' ? (int) $this->capacity : null,
            'status' => $this->status,
        ];

        $event = $this->editingEventId ? Event::query()->findOrFail($this->editingEventId) : new Event();
        $wasPublished = $event->exists && $event->status === 'published';
        $scheduleChanged = $event->exists && (
            !$event->starts_at?->equalTo($starts) || !$event->ends_at?->equalTo($ends)
        );

        if (!$event->exists) {
            $event->created_by = Auth::id();
            $event->slug = Str::slug($this->title) . '-' . Str::lower(Str::random(6));
        }
        $event->fill($data);
        $event->save();

        if ($event->status === 'published' && (!$wasPublished || $scheduleChanged)) {
            $this->notifyEligibleLearners($event, $wasPublished ? 'Lịch sự kiện đã được cập nhật' : 'Sự kiện mới đã được công bố');
        }

        $this->showForm = false;
        $this->resetForm();
        $this->dispatch('toast', message: 'Đã lưu sự kiện.', type: 'success');
    }

    public function publishEvent(int $id): void
    {
        abort_unless(Auth::user()?->isBrandAdmin(), 403);
        $event = Event::query()->findOrFail($id);
        if (!$event->course_id && !$event->expedition_id) return;
        $event->update(['status' => 'published']);
        $this->notifyEligibleLearners($event, 'Sự kiện mới đã được công bố');
        $this->dispatch('toast', message: 'Đã publish sự kiện.', type: 'success');
    }

    public function cancelEvent(int $id): void
    {
        abort_unless(Auth::user()?->isBrandAdmin(), 403);
        Event::query()->whereKey($id)->update(['status' => 'cancelled']);
        $this->dispatch('toast', message: 'Đã hủy sự kiện.', type: 'success');
    }

    public function completeEvent(int $id): void
    {
        abort_unless(Auth::user()?->isBrandAdmin(), 403);
        Event::query()->whereKey($id)->update(['status' => 'completed']);
        $this->dispatch('toast', message: 'Đã đánh dấu hoàn thành.', type: 'success');
    }

    public function deleteEvent(int $id): void
    {
        abort_unless(Auth::user()?->isBrandAdmin(), 403);
        $event = Event::query()->findOrFail($id);
        if (in_array($event->status, ['draft', 'cancelled'], true)) {
            $event->delete();
            $this->dispatch('toast', message: 'Đã xóa sự kiện.', type: 'success');
        }
    }

    public function markAttendance(int $registrationId): void
    {
        abort_unless(Auth::user()?->isBrandAdmin(), 403);
        $registration = EventRegistration::query()->findOrFail($registrationId);
        $registration->update([
            'attended_at' => $registration->attended_at ? null : now(),
            'marked_attended_by' => $registration->attended_at ? null : Auth::id(),
        ]);
    }

    public function exportCsv(int $eventId)
    {
        abort_unless(Auth::user()?->isBrandAdmin(), 403);
        $event = Event::query()->findOrFail($eventId);
        $rows = EventRegistration::query()->with('user:id,name,email')->where('event_id', $event->id)->where('status', 'registered')->latest('registered_at')->get();

        return response()->streamDownload(function () use ($event, $rows): void {
            echo "\\xEF\\xBB\\xBF";
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, ['Sự kiện', $event->title]);
            fputcsv($handle, ['Họ tên', 'Email', 'Đăng ký lúc', 'Đã tham dự']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->user?->name,
                    $row->user?->email,
                    $row->registered_at?->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i'),
                    $row->attended_at ? 'Có' : 'Chưa',
                ]);
            }
            fclose($handle);
        }, Str::slug($event->title) . '-registrations.csv');
    }

    private function notifyEligibleLearners(Event $event, string $prefix): void
    {
        $userIds = $event->course_id
            ? \App\Models\CourseEnrollment::query()->where('course_id', $event->course_id)->where('status', 'active')->pluck('user_id')
            : ExpeditionMember::query()->where('expedition_id', $event->expedition_id)->whereIn('status', ['approved', 'paid'])->whereNull('kicked_at')->pluck('user_id');

        User::query()->whereIn('id', $userIds->unique())->get()->each(function (User $user) use ($event, $prefix): void {
            $user->notify(new EventNotification($prefix . ' — DSCons', $prefix . ': ' . $event->title, route('events')));
        });
    }

    private function resetForm(): void
    {
        $this->reset(['editingEventId', 'title', 'description', 'courseId', 'expeditionId', 'startsAt', 'endsAt', 'meetingUrl', 'location', 'capacity']);
        $this->eventType = 'workshop';
        $this->format = 'online';
        $this->status = 'draft';
        $this->resetValidation();
    }

    public function render()
    {
        $events = Event::query()
            ->with(['course:id,title', 'expedition:id,title'])
            ->withCount(['registrations as registered_count' => fn($q) => $q->where('status', 'registered')])
            ->latest('starts_at')
            ->get();

        $selected = $this->selectedEventId
            ? $events->firstWhere('id', $this->selectedEventId)
            : null;
        $registrations = $selected
            ? $selected->registrations()->with('user:id,name,email')->where('status', 'registered')->latest('registered_at')->get()
            : collect();

        return view('livewire.admin-events', [
            'events' => $events,
            'courses' => Course::query()->orderBy('title')->get(['id', 'title']),
            'challenges' => Expedition::query()->orderBy('title')->get(['id', 'title']),
            'registrations' => $registrations,
            'typeLabels' => Event::typeLabels(),
        ])->layout('layouts.app', ['title' => 'Quản lý sự kiện — Admin']);
    }
}
