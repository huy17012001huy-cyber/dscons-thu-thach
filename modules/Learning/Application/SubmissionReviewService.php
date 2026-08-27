<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

use App\Core\CommunityContext;
use App\Models\ChallengeTaskCompletion;
use App\Models\ChallengeTaskReview;
use App\Models\Expedition;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Learning\Domain\Events\ChallengeSubmissionReviewed;

final class SubmissionReviewService
{
    public function __construct(private readonly CommunityContext $context) {}

    /** @param array<string, mixed>|null $rubric */
    public function approve(
        Expedition $challenge,
        int $completionId,
        User $reviewer,
        ?int $score,
        ?array $rubric,
    ): ?SubmissionReviewResult {
        return $this->review($challenge, $completionId, $reviewer, 'approved', null, $score, $rubric);
    }

    /** @param array<string, mixed>|null $rubric */
    public function reject(
        Expedition $challenge,
        int $completionId,
        User $reviewer,
        string $note,
        ?int $score,
        ?array $rubric,
    ): ?SubmissionReviewResult {
        return $this->review($challenge, $completionId, $reviewer, 'rejected', $note, $score, $rubric);
    }

    public function approveAllPending(Expedition $challenge, User $reviewer): ?BulkSubmissionReviewResult
    {
        $this->assertCurrentCommunity($challenge);

        if (! $reviewer->isCommunityAdmin($challenge->brand_id)) {
            return null;
        }

        return DB::transaction(function () use ($challenge, $reviewer): BulkSubmissionReviewResult {
            $pendingCompletions = $this->completionsForChallenge($challenge)
                ->with(['task', 'user'])
                ->where('status', 'pending')
                ->lockForUpdate()
                ->get();
            if ($pendingCompletions->isEmpty()) {
                return new BulkSubmissionReviewResult(new Collection, new Collection);
            }

            $approvedPairs = $this->completionsForChallenge($challenge)
                ->where('status', 'approved')
                ->get(['user_id', 'challenge_task_id'])
                ->map(fn (ChallengeTaskCompletion $completion): string => $this->pairKey($completion))
                ->flip();
            $awardableCompletions = new Collection;
            $processedPairs = [];

            foreach ($pendingCompletions as $completion) {
                $pairKey = $this->pairKey($completion);
                $shouldAwardXp = ! isset($approvedPairs[$pairKey]) && ! isset($processedPairs[$pairKey]);
                if ($shouldAwardXp) {
                    $awardableCompletions->push($completion);
                    $processedPairs[$pairKey] = true;
                }

                $completion->update([
                    'status' => 'approved',
                    'reviewed_by' => $reviewer->id,
                    'reviewed_at' => now(),
                    'score' => 70,
                ]);
                ChallengeTaskReview::create([
                    'completion_id' => $completion->id,
                    'reviewer_id' => $reviewer->id,
                    'status' => 'approved',
                    'note' => null,
                    'score' => 70,
                    'created_at' => now(),
                ]);
                DB::afterCommit(fn () => Event::dispatch(new ChallengeSubmissionReviewed(
                    $challenge,
                    $completion,
                    $shouldAwardXp,
                    'approved',
                    null,
                )));
            }

            return new BulkSubmissionReviewResult($pendingCompletions, $awardableCompletions);
        });
    }

    /** @param array<string, mixed>|null $rubric */
    private function review(
        Expedition $challenge,
        int $completionId,
        User $reviewer,
        string $status,
        ?string $note,
        ?int $score,
        ?array $rubric,
    ): ?SubmissionReviewResult {
        $this->assertCurrentCommunity($challenge);

        if (! $reviewer->isCommunityAdmin($challenge->brand_id)) {
            return null;
        }

        return DB::transaction(function () use ($challenge, $completionId, $reviewer, $status, $note, $score, $rubric): ?SubmissionReviewResult {
            $completion = $this->findCompletion($challenge, $completionId);
            if (! $completion || ($status === 'approved' && $completion->status === 'approved')) {
                return null;
            }

            $hasPriorApproval = ChallengeTaskCompletion::query()
                ->where('challenge_task_id', $completion->challenge_task_id)
                ->where('user_id', $completion->user_id)
                ->where('status', 'approved')
                ->where('id', '!=', $completion->id)
                ->exists();
            $isMiniGameEntry = $status === 'rejected' && $this->isMiniGameEntry($completion);
            $normalizedScore = $score === null ? null : min(100, max(0, $score));

            $attributes = [
                'status' => $status,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'review_note' => $note,
                'score' => $normalizedScore,
                'rubric_payload' => $rubric,
            ];
            if ($status === 'rejected' && ! $isMiniGameEntry) {
                $attributes['reject_count'] = DB::raw('reject_count + 1');
            }

            $completion->update($attributes);
            ChallengeTaskReview::create([
                'completion_id' => $completion->id,
                'reviewer_id' => $reviewer->id,
                'status' => $status,
                'note' => $note,
                'score' => $normalizedScore,
                'rubric_payload' => $rubric,
                'created_at' => now(),
            ]);

            $result = new SubmissionReviewResult(
                $completion->load(['task', 'user']),
                $status === 'approved' && ! $hasPriorApproval,
            );
            DB::afterCommit(fn () => Event::dispatch(new ChallengeSubmissionReviewed(
                $challenge,
                $completion,
                $result->shouldAwardXp,
                $status,
                $note,
            )));

            return $result;
        });
    }

    private function findCompletion(Expedition $challenge, int $completionId): ?ChallengeTaskCompletion
    {
        return $this->completionsForChallenge($challenge)
            ->with(['task', 'user'])
            ->whereKey($completionId)
            ->lockForUpdate()
            ->first();
    }

    /** @return Builder<ChallengeTaskCompletion> */
    private function completionsForChallenge(Expedition $challenge): Builder
    {
        return ChallengeTaskCompletion::query()
            ->whereHas('task', function (Builder $query) use ($challenge): void {
                $query->where('expedition_id', $challenge->id);
            });
    }

    private function pairKey(ChallengeTaskCompletion $completion): string
    {
        return $completion->user_id.':'.$completion->challenge_task_id;
    }

    private function isMiniGameEntry(ChallengeTaskCompletion $completion): bool
    {
        if (! $completion->task?->is_contest) {
            return false;
        }

        $firstCompletionId = ChallengeTaskCompletion::query()
            ->where('challenge_task_id', $completion->challenge_task_id)
            ->where('user_id', $completion->user_id)
            ->oldest()
            ->value('id');

        return $firstCompletionId !== $completion->id;
    }

    private function assertCurrentCommunity(Expedition $challenge): void
    {
        $brand = $this->context->current();

        if ($brand && $challenge->brand_id !== $brand->id) {
            throw new AuthorizationException('Challenge does not belong to the current community.');
        }
    }
}
