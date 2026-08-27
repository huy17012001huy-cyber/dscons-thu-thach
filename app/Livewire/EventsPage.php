<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Expedition;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\Community\Application\EventRegistrationOutcome;
use Modules\Community\Application\EventRegistrationService;

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
        if ($this->courseFilter !== '') {
            $this->challengeFilter = '';
        }
    }

    public function updatedChallengeFilter(): void
    {
        if ($this->challengeFilter !== '') {
            $this->courseFilter = '';
        }
    }

    public function registerEvent(int $eventId): void
    {
        $user = $this->currentUser();
        if (! $user) {
            return;
        }

        $outcome = app(EventRegistrationService::class)->register($eventId, $user);
        match ($outcome) {
            EventRegistrationOutcome::Registered => $this->dispatch('toast', message: 'Đăng ký tham gia thành công!', type: 'success'),
            EventRegistrationOutcome::AlreadyRegistered => $this->dispatch('toast', message: 'Bạn đã đăng ký sự kiện này rồi.', type: 'info'),
            EventRegistrationOutcome::NotEligible => $this->dispatch('toast', message: 'Bạn chưa thuộc khóa học hoặc Challenge của sự kiện này.', type: 'error'),
            EventRegistrationOutcome::Closed => $this->dispatch('toast', message: 'Sự kiện này không còn nhận đăng ký.', type: 'error'),
            EventRegistrationOutcome::Full => $this->dispatch('toast', message: 'Sự kiện đã đủ chỗ.', type: 'error'),
        };
    }

    public function cancelRegistration(int $eventId): void
    {
        $user = $this->currentUser();
        if (! $user || ! app(EventRegistrationService::class)->cancel($eventId, $user)) {
            return;
        }

        $this->dispatch('toast', message: 'Đã hủy đăng ký sự kiện.', type: 'success');
    }

    public function render(): View
    {
        $user = $this->currentUser();
        $isAdmin = $user?->isBrandAdmin() ?? false;
        $now = now();

        $query = Event::query()
            ->with(['course:id,title', 'expedition:id,title'])
            ->withCount(['registrations as registered_count' => fn ($query) => $query->where('status', 'registered')]);

        if (! $isAdmin) {
            $query->whereIn('status', ['published', 'cancelled', 'completed']);
        }

        if ($this->tab === 'past') {
            $query->where('ends_at', '<', $now);
        } else {
            $query->where('ends_at', '>=', $now);
        }

        if ($this->courseFilter !== '') {
            $query->where('course_id', (int) $this->courseFilter);
        }
        if ($this->challengeFilter !== '') {
            $query->where('expedition_id', (int) $this->challengeFilter);
        }
        if ($this->typeFilter !== '') {
            $query->where('event_type', $this->typeFilter);
        }
        if ($this->formatFilter !== '') {
            $query->where('format', $this->formatFilter);
        }

        $events = $query->orderBy($this->tab === 'past' ? 'ends_at' : 'starts_at', $this->tab === 'past' ? 'desc' : 'asc')->get();
        $registrations = $user
            ? EventRegistration::query()->where('user_id', $user->id)->whereIn('event_id', $events->pluck('id'))->get()->keyBy('event_id')
            : collect();
        $eligible = [];
        foreach ($events as $event) {
            $eligible[$event->id] = $user instanceof User && app(EventRegistrationService::class)->isEligible($event, $user);
        }

        return view('livewire.events-page', [
            'events' => $events,
            'registrations' => $registrations,
            'eligible' => $eligible,
            'isAdmin' => $isAdmin,
            'courses' => Course::query()->orderBy('title')->get(['id', 'title']),
            'challenges' => Expedition::query()->orderBy('title')->get(['id', 'title']),
            'typeLabels' => Event::typeLabels(),
        ])->layout('layouts.app', ['title' => 'Sự kiện — DSCons']);
    }

    private function currentUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }
}
