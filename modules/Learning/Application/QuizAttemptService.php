<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

use App\Core\CommunityContext;
use App\Core\Gamification\XpService;
use App\Models\ChallengeTask;
use App\Models\ExpeditionMember;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class QuizAttemptService
{
    public function __construct(
        private readonly CommunityContext $context,
        private readonly XpService $xp,
    ) {}

    public function answer(ChallengeTask $task, User $user, int $questionIndex, string $letter): QuizAnswerResult
    {
        if (! in_array($letter, ['A', 'B', 'C', 'D'], true)) {
            throw new InvalidArgumentException('Unsupported quiz answer.');
        }

        return DB::transaction(function () use ($task, $user, $questionIndex, $letter): QuizAnswerResult {
            $brand = $this->context->require();
            $task = ChallengeTask::query()
                ->with('expedition')
                ->whereKey($task->id)
                ->whereHas('expedition', fn ($query) => $query->where('brand_id', $brand->id))
                ->lockForUpdate()
                ->firstOrFail();
            $challenge = $task->expedition;
            if (! $challenge) {
                throw new AuthorizationException('Challenge task is unavailable.');
            }
            $member = ExpeditionMember::query()
                ->where('brand_id', $brand->id)
                ->where('expedition_id', $challenge->id)
                ->where('user_id', $user->id)
                ->whereIn('status', ['approved', 'paid'])
                ->whereNull('kicked_at')
                ->lockForUpdate()
                ->first();
            if (! $member || $task->day_number > $challenge->getCurrentDayForMember($member)) {
                throw new AuthorizationException('Challenge task is not available to this member.');
            }

            $questions = $task->quiz_json ?? [];
            if (! isset($questions[$questionIndex])) {
                throw new InvalidArgumentException('Quiz question does not exist.');
            }
            $isCorrect = $letter === ($questions[$questionIndex]['correct'] ?? null);
            $created = QuizAttempt::query()->insertOrIgnore([
                'user_id' => $user->id,
                'challenge_task_id' => $task->id,
                'question_index' => $questionIndex,
                'selected_letter' => $letter,
                'is_correct' => $isCorrect,
                'xp_awarded' => $isCorrect,
                'answered_at' => now(),
            ]) === 1;
            $attempt = QuizAttempt::query()
                ->where('user_id', $user->id)
                ->where('challenge_task_id', $task->id)
                ->where('question_index', $questionIndex)
                ->firstOrFail();
            if ($created && $isCorrect) {
                DB::afterCommit(fn () => $this->xp->award(
                    $user,
                    'quiz_correct',
                    1.0,
                    'Quiz Day '.$task->day_number.' · Câu '.($questionIndex + 1),
                    $task,
                ));
            }

            return new QuizAnswerResult(
                $attempt->selected_letter,
                (bool) $attempt->is_correct,
                $created,
                $created && $isCorrect,
            );
        });
    }
}
