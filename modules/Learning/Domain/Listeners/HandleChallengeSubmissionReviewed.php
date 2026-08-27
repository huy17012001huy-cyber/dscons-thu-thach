<?php

declare(strict_types=1);

namespace Modules\Learning\Domain\Listeners;

use App\Core\CommunityContext;
use App\Core\Gamification\XpService;
use App\Models\ChallengeSubmissionXpAward;
use App\Models\ChallengeTask;
use App\Models\ChallengeTaskCompletion;
use App\Models\Expedition;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Support\Facades\DB;
use Modules\Learning\Application\ChallengeCompletionNotificationService;
use Modules\Learning\Domain\Events\ChallengeSubmissionReviewed;

final class HandleChallengeSubmissionReviewed implements ShouldQueueAfterCommit
{
    public function __construct(
        private readonly ChallengeCompletionNotificationService $completionNotifications,
        private readonly CommunityContext $context,
        private readonly XpService $xp,
    ) {}

    public function handle(ChallengeSubmissionReviewed $event): void
    {
        $challenge = Expedition::withoutGlobalScopes()->with('brand')->find($event->challenge->id);
        $brand = $challenge?->brand;
        if (! $challenge || ! $brand) {
            return;
        }

        $this->context->run($brand, function () use ($brand, $challenge, $event): void {
            $completion = ChallengeTaskCompletion::query()
                ->with(['task', 'user'])
                ->whereKey($event->completion->id)
                ->whereHas('task', fn ($query) => $query->where('expedition_id', $challenge->id))
                ->first();
            $user = $completion?->user;
            $task = $completion?->task;
            if (! $completion || ! $user instanceof User || ! $task instanceof ChallengeTask) {
                return;
            }

            if ($event->status === 'approved') {
                $this->awardXpOnce($challenge, $completion, $user, $task, $event->shouldAwardXp);
                $user->notify(new GenericNotification(
                    '✓',
                    'Bài nộp đã được duyệt!',
                    route('community.challenge.show', ['community' => $brand->slug, 'slug' => $challenge->slug]),
                ));
                $this->completionNotifications->notifyIfCompleted($challenge, $user);

                return;
            }

            $user->notify(new GenericNotification(
                '✗',
                'Bài nộp bị từ chối: '.($event->note ?: 'Bài nộp chưa đạt yêu cầu').'. Vui lòng nộp lại.',
                route('community.challenge.show', ['community' => $brand->slug, 'slug' => $challenge->slug]),
            ));
        });
    }

    private function awardXpOnce(Expedition $challenge, ChallengeTaskCompletion $completion, User $user, ChallengeTask $task, bool $shouldAwardXp): void
    {
        if (! $shouldAwardXp) {
            return;
        }

        DB::transaction(function () use ($challenge, $completion, $task, $user): void {
            $created = ChallengeSubmissionXpAward::query()->insertOrIgnore([
                'challenge_task_completion_id' => $completion->id,
                'user_id' => $completion->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            if ($created !== 1) {
                return;
            }

            $this->xp->award(
                $user,
                'expedition_checkin',
                1.0,
                'Hoàn thành nhiệm vụ ngày '.$task->day_number.': '.$task->title,
                $challenge,
            );
        });
    }
}
