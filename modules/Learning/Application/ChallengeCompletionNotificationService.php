<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

use App\Core\Notifications\TelegramService;
use App\Mail\ChallengeCompletionMail;
use App\Models\ChallengeTask;
use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

final class ChallengeCompletionNotificationService
{
    public function notifyIfCompleted(Expedition $challenge, User $user): void
    {
        $payload = DB::transaction(function () use ($challenge, $user): ?array {
            $member = ExpeditionMember::query()
                ->where('expedition_id', $challenge->id)
                ->where('brand_id', $challenge->brand_id)
                ->where('user_id', $user->id)
                ->whereIn('status', ['approved', 'paid'])
                ->whereNull('kicked_at')
                ->whereNull('completion_notified_at')
                ->lockForUpdate()
                ->first();
            if (! $member) {
                return null;
            }

            $tasks = $challenge->tasks()->orderBy('day_number')->get(['id', 'day_number', 'title', 'label']);
            if ($tasks->isEmpty()) {
                return null;
            }

            $days = $this->completionDays($tasks->pluck('id')->map(static fn (mixed $id): int => (int) $id)->all(), $user->id, $tasks);
            if ($days === null) {
                return null;
            }
            /** @var list<array{day:int,label:string,is_late:bool}> $days */
            $member->update(['completion_notified_at' => now()]);

            return ['days' => $days, 'late_count' => collect($days)->where('is_late', true)->count()];
        });
        if ($payload === null) {
            return;
        }

        $this->sendCompletionMail($challenge, $user, $payload['days']);
        $summary = $payload['late_count'] > 0 ? "(có {$payload['late_count']} ngày trễ)" : '(không có ngày trễ)';
        TelegramService::sendCompletion(
            '🏆 <b>Hoàn thành '.$challenge->required_days." ngày</b>\n"
            ."Member {$user->name} ({$user->email}) đã hoàn thành thử thách {$summary}.",
        );
    }

    /**
     * @param  array<int, int>  $taskIds
     * @param  Collection<int, ChallengeTask>  $tasks
     * @return list<array{day:int,label:string,is_late:bool}>|null
     */
    private function completionDays(array $taskIds, int $userId, Collection $tasks): ?array
    {
        $mainCompletions = DB::table('challenge_task_completions')
            ->whereIn('challenge_task_id', $taskIds)
            ->where('user_id', $userId)
            ->orderBy('created_at')
            ->get(['challenge_task_id', 'status', 'is_late'])
            ->groupBy('challenge_task_id')
            ->map(fn ($rows) => $rows->first());
        $days = [];

        foreach ($tasks as $task) {
            $completion = $mainCompletions->get($task->id);
            if (! $completion || $completion->status !== 'approved') {
                return null;
            }
            $days[] = [
                'day' => (int) $task->day_number,
                'label' => (string) ($task->title ?: $task->label ?: ('Ngày '.$task->day_number)),
                'is_late' => (bool) $completion->is_late,
            ];
        }

        return $days;
    }

    /** @param list<array{day:int,label:string,is_late:bool}> $days */
    private function sendCompletionMail(Expedition $challenge, User $user, array $days): void
    {
        $brand = $challenge->brand;
        if ($user->email === '' || $brand === null) {
            return;
        }

        try {
            Mail::to($user->email)->send(new ChallengeCompletionMail(
                userName: $user->name,
                challengeTitle: $challenge->title,
                brandName: config('app.name'),
                days: $days,
                completedAt: now()->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y'),
                challengeUrl: route('community.challenge.show', ['community' => $brand->slug, 'slug' => $challenge->slug]),
            ));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
