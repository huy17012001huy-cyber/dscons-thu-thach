<?php

namespace App\Livewire;

use App\Models\ChallengeTask;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class MeetingBanner extends Component
{
    public function render(): View
    {
        // Find upcoming meetings (within next 24h) or ongoing (started within last 2h)
        $now = now();
        $meetings = ChallengeTask::whereNotNull('meeting_at')
            ->where('meeting_at', '>=', $now->copy()->subHours(2))
            ->where('meeting_at', '<=', $now->copy()->addHours(24))
            ->with('expedition')
            ->orderBy('meeting_at')
            ->get();

        if ($meetings->isEmpty()) {
            return view('livewire.meeting-banner', ['meetings' => collect(), 'unlockedTaskIds' => []]);
        }

        // Check which tasks the current user has unlocked
        $unlockedTaskIds = [];
        $user = Auth::user();
        if ($user instanceof User) {
            foreach ($meetings as $meeting) {
                $expedition = $meeting->expedition;
                if (! $expedition) {
                    continue;
                }

                $member = $expedition->members()
                    ->where('user_id', $user->id)
                    ->whereIn('status', ['approved', 'paid'])
                    ->whereNull('kicked_at')
                    ->whereNotNull('personal_starts_at')
                    ->first();

                if (! $member) {
                    continue;
                }

                if (! $member->personal_starts_at) {
                    continue;
                }

                $hoursElapsed = $member->personal_starts_at->diffInHours($now);
                $timeDay = min((int) floor($hoursElapsed / 24) + 1, $expedition->required_days);

                $completedCount = DB::table('challenge_task_completions')
                    ->join('challenge_tasks', 'challenge_tasks.id', '=', 'challenge_task_completions.challenge_task_id')
                    ->where('challenge_tasks.expedition_id', $expedition->id)
                    ->where('challenge_task_completions.user_id', $user->id)
                    ->count();

                $currentDay = min($timeDay, $completedCount + 1);

                if ($meeting->day_number <= $currentDay) {
                    $unlockedTaskIds[] = $meeting->id;
                }
            }
        }

        return view('livewire.meeting-banner', [
            'meetings' => $meetings,
            'unlockedTaskIds' => $unlockedTaskIds,
        ]);
    }
}
