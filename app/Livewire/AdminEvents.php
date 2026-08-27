<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Expedition;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Livewire\Component;
use Modules\Community\Application\EventManagementData;
use Modules\Community\Application\EventManagementService;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function updatedCourseId(int|string|null $value): void
    {
        if (filled($value)) {
            $this->expeditionId = null;
        }
    }

    public function updatedExpeditionId(int|string|null $value): void
    {
        if (filled($value)) {
            $this->courseId = null;
        }
    }

    public function openCreate(): void
    {
        $this->authorizeAdmin();
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $this->authorizeAdmin();
        $event = Event::query()->findOrFail($id);
        $this->editingEventId = $event->id;
        $this->title = $event->title;
        $this->description = $event->description ?? '';
        $this->eventType = $event->event_type;
        $this->format = $event->format;
        $this->courseId = $event->course_id;
        $this->expeditionId = $event->expedition_id;
        $this->startsAt = $event->starts_at?->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d\TH:i') ?? '';
        $this->endsAt = $event->ends_at?->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d\TH:i') ?? '';
        $this->meetingUrl = $event->meeting_url ?? '';
        $this->location = $event->location ?? '';
        $this->capacity = $event->capacity === null ? '' : (string) $event->capacity;
        $this->status = $event->status;
        $this->showForm = true;
    }

    public function saveEvent(): void
    {
        $this->authorizeAdmin();
        $this->validate($this->rules());
        if (($this->courseId === null) === ($this->expeditionId === null)) {
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

        try {
            app(EventManagementService::class)->save($this->editingEventId, $this->currentUser(), new EventManagementData(
                title: trim($this->title),
                description: trim($this->description) ?: null,
                eventType: $this->eventType,
                format: $this->format,
                startsAt: $starts,
                endsAt: $ends,
                status: $this->status,
                courseId: $this->courseId,
                expeditionId: $this->expeditionId,
                meetingUrl: trim($this->meetingUrl) ?: null,
                location: trim($this->location) ?: null,
                capacity: $this->capacity === '' ? null : (int) $this->capacity,
            ));
        } catch (InvalidArgumentException $exception) {
            $this->addError('courseId', $exception->getMessage());

            return;
        }

        $this->showForm = false;
        $this->resetForm();
        $this->dispatch('toast', message: 'Đã lưu sự kiện.', type: 'success');
    }

    public function publishEvent(int $id): void
    {
        $this->authorizeAdmin();
        if (app(EventManagementService::class)->publish($id, $this->currentUser())) {
            $this->dispatch('toast', message: 'Đã publish sự kiện.', type: 'success');
        }
    }

    public function cancelEvent(int $id): void
    {
        $this->authorizeAdmin();
        app(EventManagementService::class)->updateStatus($id, $this->currentUser(), 'cancelled');
        $this->dispatch('toast', message: 'Đã hủy sự kiện.', type: 'success');
    }

    public function completeEvent(int $id): void
    {
        $this->authorizeAdmin();
        app(EventManagementService::class)->updateStatus($id, $this->currentUser(), 'completed');
        $this->dispatch('toast', message: 'Đã đánh dấu hoàn thành.', type: 'success');
    }

    public function deleteEvent(int $id): void
    {
        $this->authorizeAdmin();
        if (app(EventManagementService::class)->delete($id, $this->currentUser())) {
            $this->dispatch('toast', message: 'Đã xóa sự kiện.', type: 'success');
        }
    }

    public function markAttendance(int $registrationId): void
    {
        $this->authorizeAdmin();
        app(EventManagementService::class)->toggleAttendance($registrationId, $this->currentUser());
    }

    public function exportCsv(int $eventId): StreamedResponse
    {
        $this->authorizeAdmin();
        $event = Event::query()->findOrFail($eventId);
        $rows = EventRegistration::query()
            ->with('user:id,name,email')
            ->where('brand_id', $event->brand_id)
            ->where('event_id', $event->id)
            ->where('status', 'registered')
            ->latest('registered_at')
            ->get();

        return response()->streamDownload(function () use ($event, $rows): void {
            echo "\xEF\xBB\xBF";
            $handle = fopen('php://output', 'wb');
            if ($handle === false) {
                return;
            }
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
        }, Str::slug($event->title).'-registrations.csv');
    }

    public function render(): View
    {
        $events = Event::query()
            ->with(['course:id,title', 'expedition:id,title'])
            ->withCount(['registrations as registered_count' => fn ($query) => $query->where('status', 'registered')])
            ->latest('starts_at')
            ->get();
        $selected = $this->selectedEventId ? $events->firstWhere('id', $this->selectedEventId) : null;
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

    /** @return array<string, mixed> */
    private function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'eventType' => 'required|in:workshop,live,mentor,offline,other',
            'format' => 'required|in:online,offline',
            'courseId' => ['nullable', 'integer', Rule::exists('courses', 'id')->where('brand_id', brand()->id)],
            'expeditionId' => ['nullable', 'integer', Rule::exists('expeditions', 'id')->where('brand_id', brand()->id)],
            'startsAt' => 'required|date',
            'endsAt' => 'required|date',
            'meetingUrl' => 'nullable|url|max:2000',
            'location' => 'nullable|string|max:500',
            'capacity' => 'nullable|integer|min:1|max:1000000',
            'status' => 'required|in:draft,published,cancelled,completed',
        ];
    }

    private function resetForm(): void
    {
        $this->reset(['editingEventId', 'title', 'description', 'courseId', 'expeditionId', 'startsAt', 'endsAt', 'meetingUrl', 'location', 'capacity']);
        $this->eventType = 'workshop';
        $this->format = 'online';
        $this->status = 'draft';
        $this->resetValidation();
    }

    private function authorizeAdmin(): void
    {
        abort_unless($this->currentUser()->isCommunityAdmin(brand()->id), 403);
    }

    private function currentUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        return $user;
    }
}
