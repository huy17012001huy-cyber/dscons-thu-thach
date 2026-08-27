<?php

declare(strict_types=1);

namespace Modules\Learning\Domain\Listeners;

use App\Core\CommunityContext;
use App\Core\Notifications\TelegramService;
use App\Models\ChallengeTask;
use App\Models\Expedition;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Modules\Learning\Domain\Events\ChallengeSubmissionCreated;

final class NotifyChallengeSubmissionCreated implements ShouldQueueAfterCommit
{
    public function __construct(private readonly CommunityContext $context) {}

    public function handle(ChallengeSubmissionCreated $event): void
    {
        $challenge = Expedition::withoutGlobalScopes()->with('brand')->find($event->challengeId);
        $brand = $challenge?->brand;
        $learner = User::query()->find($event->learnerId);
        if (! $challenge || ! $brand || ! $learner) {
            return;
        }

        $this->context->run($brand, function () use ($challenge, $event, $learner): void {
            $task = ChallengeTask::query()
                ->whereKey($event->taskId)
                ->where('expedition_id', $challenge->id)
                ->first();
            if (! $task) {
                return;
            }

            TelegramService::sendToAdmin($this->message($challenge, $task, $learner, $event));
        });
    }

    private function message(
        Expedition $challenge,
        ChallengeTask $task,
        User $learner,
        ChallengeSubmissionCreated $event,
    ): string {
        $title = $event->isContest ? '🏆 <b>Mini-game</b>' : '📝 <b>Bài nộp mới</b>';
        $late = $event->isLate ? ($event->isContest ? "⚠️ Trễ\n" : "⚠️ Nộp trễ\n") : '';

        return $title."\n"
            ."👤 {$learner->name}\n"
            ."📋 Ngày {$task->day_number}: {$task->title}\n"
            .$late
            .'🔗 '.route('challenge.show', $challenge->slug);
    }
}
