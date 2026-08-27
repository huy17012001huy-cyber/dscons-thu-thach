<?php

namespace App\Livewire;

use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\ChallengeTask;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\Learning\Application\ChallengeAccessService;

class ChallengeLessonPage extends Component
{
    public Expedition $expedition;
    public ChallengeTask $task;
    public int $day;

    public function mount(string $slug, int $day): void
    {
        $this->day = $day;
        $this->expedition = app(ChallengeAccessService::class)->find($slug);
        $this->expedition->load(['leader', 'members.user', 'tasks']);
        $task = $this->expedition->tasks->first(fn (ChallengeTask $candidate): bool => (int) $candidate->day_number === $day);
        abort_unless($task !== null, 404);
        $this->task = $task;

        $user = Auth::user();
        $isAdmin = $user instanceof User && $user->isBrandAdmin();
        $member = $user instanceof User
            ? $this->expedition->members->first(fn (ExpeditionMember $candidate): bool => (int) $candidate->user_id === (int) $user->id)
            : null;
        abort_unless(app(ChallengeAccessService::class)->canAccessDay($this->expedition, $day, $user), 403);
    }

    public function render(): View
    {
        $payload = is_array($this->task->instruction_payload) ? $this->task->instruction_payload : [];
        $previous = $this->expedition->tasks->where('day_number', '<', $this->day)->sortByDesc('day_number')->first();
        $next = $this->expedition->tasks->where('day_number', '>', $this->day)->sortBy('day_number')->first();

        return view('livewire.challenge-lesson-page', [
            'payload' => $payload,
            'previous' => $previous,
            'next' => $next,
        ])->layout('layouts.app', ['title' => 'Ngày '.$this->day.' · '.$this->expedition->title]);
    }
}
