<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\ExpeditionMember;
use App\Models\CourseEnrollment;
use App\Notifications\EventNotification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

class EventsPage extends Component
{
    #[Url]
    public string $tab = 'upcoming';

    #[Url]
    public string $courseFilter = '';

    #[Url]
    public string $challengeFilter = '';

    #[Url]
    public string $typeFilter = '';

    #[Url]
    public string $formatFilter = '';

    public function updatedCourseFilter(): void
    {
        if ($this->courseFilter !== '') $this->challengeFilter = '';
    }

    public function updatedChallengeFilter(): void
    {
        if ($this->challengeFilter !== '') $this->courseFilter = '';
    }

    public function registerEvent(int $eventId): void
    {
        $user = Auth::user();
        if (!$user) return;

        $event = Event::query()->findOrFail($eventId);
        if (!$this->isEligible($event, $user)) {
            $this->dispatch('toast', message: 'Bạn chưa thuộc khóa học hoặc Challenge của sự kiện này.', type: 'error');
            return;
        }

        if ($event->status !== 'published' || $event->ends_at?->isPast()) {
            $this->dispatch('toast', message: 'Sự kiện này không còn nhận đăng ký.', type: 'error');
            return;
        }

        try {
            DB::transaction(function () use ($event, $user): void {
                $event = Event::query()->lockForUpdate()->findOrFail($event->id);
                $registration = EventRegistration::query()
                    ->where('event_id', $event->id)
                    ->where('user_id', $user->id)
                    ->first();

                if ($registration?->status === 'registered') {
                    return;
                }

                $registeredCount = EventRegistration::query()
                    ->where('event_id', $event->id)
                    ->where('status', 'registered')
                    ->count();
                if ($event->capacity !== null && $registeredCount >= $event->capacity) {
                    throw new \RuntimeException('Sự kiện đã đủ chỗ.');
                }

                if ($registration) {
                    $registration->update([
                        'status' => 'registered',
                        'registered_at' => now(),
                    ]);
                } else {
                    EventRegistration::create([
                        'event_id' => $event->id,
                        'user_id' => $user->id,
                        'status' => 'registered',
                        'registered_at' => now(),
                    ]);
                }
            });
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
            return;
        } catch (QueryException $e) {
            $this->dispatch('toast', message: 'Bạn đã đăng ký sự kiện này rồi.', type: 'info');
            return;
        }

        $this->dispatch('toast', message: 'Đăng ký tham gia thành công!', type: 'success');
        $url = app()->bound('brand') ? community_route('events') : route('events');
        $user->notify(new EventNotification('Đăng ký sự kiện — ' . (app()->bound('brand') ? brand()->name : 'DSCons'), 'Bạn đã đăng ký sự kiện “' . $event->title . '”.', $url));
    }

    public function cancelRegistration(int $eventId): void
    {
        $registration = EventRegistration::query()
            ->where('event_id', $eventId)
            ->where('user_id', Auth::id())
            ->where('status', 'registered')
            ->first();

        if (!$registration) return;
        $registration->update(['status' => 'cancelled']);
        $this->dispatch('toast', message: 'Đã hủy đăng ký sự kiện.', type: 'success');
    }

    private function isEligible(Event $event, $user): bool
    {
        if (!$user) return false;
        if ($user->isBrandAdmin()) return true;
        if ($user->hasPremiumMembership()) return true;

        if ($event->course_id) {
            return CourseEnrollment::query()
                ->where('course_id', $event->course_id)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->exists();
        }

        if ($event->expedition_id) {
            return ExpeditionMember::query()
                ->where('expedition_id', $event->expedition_id)
                ->where('user_id', $user->id)
                ->whereIn('status', ['approved', 'paid'])
                ->whereNull('kicked_at')
                ->exists();
        }

        return false;
    }

    public function render()
    {
        $user = Auth::user();
        $isAdmin = $user?->isBrandAdmin() ?? false;
        $now = now();

        $query = Event::query()
            ->with(['course:id,title', 'expedition:id,title'])
            ->withCount(['registrations as registered_count' => fn($q) => $q->where('status', 'registered')]);

        if (!$isAdmin) {
            // Cancelled/completed events remain visible as history, while drafts
            // stay private to admins.
            $query->whereIn('status', ['published', 'cancelled', 'completed']);
        }

        if ($this->tab === 'past') {
            $query->where('ends_at', '<', $now);
        } else {
            $query->where('ends_at', '>=', $now);
        }

        if ($this->courseFilter !== '') $query->where('course_id', (int) $this->courseFilter);
        if ($this->challengeFilter !== '') $query->where('expedition_id', (int) $this->challengeFilter);
        if ($this->typeFilter !== '') $query->where('event_type', $this->typeFilter);
        if ($this->formatFilter !== '') $query->where('format', $this->formatFilter);

        $events = $query->orderBy($this->tab === 'past' ? 'ends_at' : 'starts_at', $this->tab === 'past' ? 'desc' : 'asc')->get();
        $registrations = $user
            ? EventRegistration::query()->where('user_id', $user->id)->whereIn('event_id', $events->pluck('id'))->get()->keyBy('event_id')
            : collect();

        $eligible = [];
        foreach ($events as $event) {
            $eligible[$event->id] = $this->isEligible($event, $user);
        }

        return view('livewire.events-page', [
            'events' => $events,
            'registrations' => $registrations,
            'eligible' => $eligible,
            'isAdmin' => $isAdmin,
            'courses' => \App\Models\Course::query()->orderBy('title')->get(['id', 'title']),
            'challenges' => \App\Models\Expedition::query()->orderBy('title')->get(['id', 'title']),
            'typeLabels' => Event::typeLabels(),
        ])->layout('layouts.app', ['title' => 'Sự kiện — DSCons']);
    }
}
