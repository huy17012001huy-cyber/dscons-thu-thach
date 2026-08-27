<?php

namespace App\Livewire;

use App\Models\Expedition;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ChallengePage extends Component
{
    use WithPagination;

    #[Url]
    public string $tab = 'active';

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['active', 'completed', 'discover'], true)) {
            return;
        }

        $this->tab = $tab;
        $this->resetPage();
    }

    public function render(): View
    {
        $userId = Auth::id();
        $isAdmin = Auth::user()?->isBrandAdmin() ?? false;

        $activeIds = $isAdmin
            ? Expedition::whereIn('status', ['active', 'open'])->pluck('id')
            : Expedition::query()
                ->whereHas('members', function ($query) use ($userId): void {
                    $query->where('user_id', $userId)
                        ->whereIn('status', ['approved', 'paid'])
                        ->whereNotNull('personal_starts_at')
                        ->whereNull('completed_at')
                        ->whereNull('kicked_at');
                })
                ->pluck('id');

        $completedIds = $isAdmin
            ? Expedition::where('status', 'completed')->pluck('id')
            : Expedition::query()
                ->whereHas('members', function ($query) use ($userId): void {
                    $query->where('user_id', $userId)->whereNotNull('completed_at');
                })
                ->pluck('id');

        $query = Expedition::query()
            ->withCount([
                'members as active_members_count' => fn ($q) => $q
                    ->whereIn('status', ['approved', 'paid'])
                    ->whereNull('kicked_at'),
                'tasks',
            ])
            ->with([
                'leader',
                'tasks:id,expedition_id,day_number',
                'members' => fn ($q) => $q->where('user_id', $userId),
            ])
            ->whereIn('status', $this->tab === 'completed'
                ? ['active', 'open', 'completed', 'failed', 'cancelled']
                : ['active', 'open']);

        match ($this->tab) {
            'active' => $query->whereIn('id', $activeIds),
            'completed' => $query->whereIn('id', $completedIds),
            default => $query->whereNotIn('id', $activeIds->merge($completedIds)->unique()),
        };

        $challenges = $query->latest()->paginate(12);

        $taskIds = $challenges->getCollection()
            ->flatMap(fn (Expedition $challenge) => $challenge->tasks->map(fn ($task): int => (int) $task->id))
            ->values();

        $approvedTaskIds = collect();
        if ($userId && $taskIds->isNotEmpty()) {
            $approvedTaskIds = DB::table('challenge_task_completions')
                ->where('user_id', $userId)
                ->whereIn('challenge_task_id', $taskIds)
                ->where('status', 'approved')
                ->orderBy('created_at')
                ->pluck('challenge_task_id')
                ->unique();
        }

        $challenges->getCollection()->transform(function (Expedition $challenge) use ($approvedTaskIds): Expedition {
            $member = $challenge->members->first();
            $taskIds = $challenge->tasks->pluck('id');
            $completed = $approvedTaskIds->intersect($taskIds)->count();
            $total = (int) ($challenge->tasks_count ?? $taskIds->count());

            $challenge->setAttribute('current_member', $member);
            $challenge->setAttribute('progress_completed', $completed);
            $challenge->setAttribute('progress_total', $total);
            $challenge->setAttribute('progress_percent', $total > 0 ? (int) round($completed / $total * 100) : 0);

            return $challenge;
        });

        return view('livewire.challenge-page', [
            'challenges' => $challenges,
            'activeCount' => $activeIds->count(),
            'completedCount' => $completedIds->count(),
        ])->layout('layouts.app', ['title' => 'Challenge — '.brand()->name]);
    }
}
