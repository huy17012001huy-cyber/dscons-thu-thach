<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

use App\Core\CommunityContext;
use App\Models\ChallengeTask;
use App\Models\ChallengeTaskCompletion;
use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Learning\Domain\Events\ChallengeSubmissionCreated;

final class ChallengeSubmissionService
{
    public function __construct(private readonly CommunityContext $context) {}

    /** @param array<string, mixed>|null $payload */
    public function submit(
        Expedition $challenge,
        int $taskId,
        User $user,
        string $evidence,
        ?array $payload,
    ): ChallengeSubmissionResult {
        return DB::transaction(function () use ($challenge, $taskId, $user, $evidence, $payload): ChallengeSubmissionResult {
            $task = $this->taskForChallenge($challenge, $taskId);
            $outcome = $this->submissionAvailability($challenge, $task, $user);
            if ($outcome) {
                return new ChallengeSubmissionResult($outcome, $task);
            }
            if (blank($evidence)) {
                return new ChallengeSubmissionResult(ChallengeSubmissionOutcome::MissingEvidence, $task);
            }
            if (ChallengeTaskCompletion::query()
                ->where('challenge_task_id', $task->id)
                ->where('user_id', $user->id)
                ->exists()) {
                return new ChallengeSubmissionResult(ChallengeSubmissionOutcome::AlreadySubmitted, $task);
            }

            $member = $this->requireApprovedMember($challenge, $user);
            $isLate = $challenge->isTaskLateForMember($member, $task->day_number);
            ChallengeTaskCompletion::create([
                'challenge_task_id' => $task->id,
                'user_id' => $user->id,
                'evidence' => $evidence,
                'status' => 'pending',
                'is_late' => $isLate,
                'submission_payload' => $payload,
            ]);
            DB::afterCommit(fn () => Event::dispatch(new ChallengeSubmissionCreated(
                $challenge->id,
                $task->id,
                $user->id,
                false,
                $isLate,
            )));

            return new ChallengeSubmissionResult(ChallengeSubmissionOutcome::Submitted, $task, $isLate);
        });
    }

    /** @param array<string, mixed>|null $payload */
    public function resubmit(
        Expedition $challenge,
        int $taskId,
        User $user,
        string $evidence,
        ?array $payload,
    ): ChallengeSubmissionResult {
        return DB::transaction(function () use ($challenge, $taskId, $user, $evidence, $payload): ChallengeSubmissionResult {
            $task = $this->taskForChallenge($challenge, $taskId);
            $outcome = $this->submissionAvailability($challenge, $task, $user);
            if ($outcome) {
                return new ChallengeSubmissionResult($outcome, $task);
            }
            if (blank($evidence)) {
                return new ChallengeSubmissionResult(ChallengeSubmissionOutcome::MissingEvidence, $task);
            }

            $completion = ChallengeTaskCompletion::query()
                ->where('challenge_task_id', $task->id)
                ->where('user_id', $user->id)
                ->oldest()
                ->lockForUpdate()
                ->first();
            if (! $completion || $completion->status !== 'rejected') {
                return new ChallengeSubmissionResult(ChallengeSubmissionOutcome::NotRejected, $task);
            }

            $member = $this->requireApprovedMember($challenge, $user);
            $isLate = $challenge->isTaskLateForMember($member, $task->day_number);
            $completion->update([
                'evidence' => $evidence,
                'status' => 'pending',
                'is_late' => $isLate,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'review_note' => null,
                'submission_payload' => $payload,
            ]);

            return new ChallengeSubmissionResult(ChallengeSubmissionOutcome::Resubmitted, $task, $isLate);
        });
    }

    public function submitContestEntry(
        Expedition $challenge,
        int $taskId,
        User $user,
        string $evidence,
    ): ChallengeSubmissionResult {
        return DB::transaction(function () use ($challenge, $taskId, $user, $evidence): ChallengeSubmissionResult {
            $task = $this->taskForChallenge($challenge, $taskId);
            if (! $task->is_contest) {
                return new ChallengeSubmissionResult(ChallengeSubmissionOutcome::NotContest, $task);
            }

            $outcome = $this->submissionAvailability($challenge, $task, $user);
            if ($outcome) {
                return new ChallengeSubmissionResult($outcome, $task);
            }

            $member = $this->requireApprovedMember($challenge, $user);
            if ($challenge->isTaskLateForMember($member, $task->day_number)) {
                return new ChallengeSubmissionResult(ChallengeSubmissionOutcome::SubmissionClosed, $task, true);
            }

            $completions = ChallengeTaskCompletion::query()
                ->where('challenge_task_id', $task->id)
                ->where('user_id', $user->id)
                ->oldest()
                ->lockForUpdate()
                ->get();
            $mainSubmission = $completions->first();
            if (! $mainSubmission) {
                return new ChallengeSubmissionResult(ChallengeSubmissionOutcome::MainSubmissionMissing, $task);
            }
            if ($mainSubmission->status === 'pending') {
                return new ChallengeSubmissionResult(ChallengeSubmissionOutcome::MainSubmissionPending, $task);
            }
            if ($mainSubmission->status === 'rejected') {
                return new ChallengeSubmissionResult(ChallengeSubmissionOutcome::MainSubmissionRejected, $task);
            }

            $latestEntry = $completions->slice(1)->last();
            if ($latestEntry?->status === 'pending') {
                return new ChallengeSubmissionResult(ChallengeSubmissionOutcome::ContestEntryPending, $task);
            }
            if (blank($evidence)) {
                return new ChallengeSubmissionResult(ChallengeSubmissionOutcome::MissingEvidence, $task);
            }

            if ($latestEntry?->status === 'rejected') {
                $latestEntry->update([
                    'evidence' => $evidence,
                    'status' => 'pending',
                    'is_late' => false,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'review_note' => null,
                ]);
            } else {
                ChallengeTaskCompletion::create([
                    'challenge_task_id' => $task->id,
                    'user_id' => $user->id,
                    'evidence' => $evidence,
                    'status' => 'pending',
                    'is_late' => false,
                ]);
            }
            DB::afterCommit(fn () => Event::dispatch(new ChallengeSubmissionCreated(
                $challenge->id,
                $task->id,
                $user->id,
                true,
                false,
            )));

            return new ChallengeSubmissionResult(ChallengeSubmissionOutcome::ContestSubmitted, $task);
        });
    }

    private function taskForChallenge(Expedition $challenge, int $taskId): ChallengeTask
    {
        $this->assertCurrentCommunity($challenge);

        return ChallengeTask::query()
            ->whereKey($taskId)
            ->where('expedition_id', $challenge->id)
            ->lockForUpdate()
            ->firstOr(fn () => throw (new ModelNotFoundException)->setModel(ChallengeTask::class, [$taskId]));
    }

    private function submissionAvailability(
        Expedition $challenge,
        ChallengeTask $task,
        User $user,
    ): ?ChallengeSubmissionOutcome {
        if ($this->isFrozenFor($challenge, $task, $user)) {
            return ChallengeSubmissionOutcome::Frozen;
        }
        if ($task->locked_until && now()->lessThan($task->locked_until)) {
            return ChallengeSubmissionOutcome::TaskLocked;
        }

        $member = $this->findApprovedMember($challenge, $user);
        if (! $member) {
            return ChallengeSubmissionOutcome::NotEnrolled;
        }
        if ($task->day_number > $challenge->getCurrentDayForMember($member)) {
            return ChallengeSubmissionOutcome::NotUnlocked;
        }

        return null;
    }

    private function requireApprovedMember(Expedition $challenge, User $user): ExpeditionMember
    {
        return $this->findApprovedMember($challenge, $user)
            ?? throw new ModelNotFoundException('Approved challenge enrollment was not found.');
    }

    private function findApprovedMember(Expedition $challenge, User $user): ?ExpeditionMember
    {
        return ExpeditionMember::query()
            ->where('expedition_id', $challenge->id)
            ->where('brand_id', $challenge->brand_id)
            ->where('user_id', $user->id)
            ->whereIn('status', ['approved', 'paid'])
            ->whereNull('kicked_at')
            ->lockForUpdate()
            ->first();
    }

    private function isFrozenFor(Expedition $challenge, ChallengeTask $task, User $user): bool
    {
        return $challenge->isFreezeActive()
            && $task->day_number >= $challenge->freeze_from_day
            && ! $user->isCommunityAdmin($challenge->brand_id)
            && $challenge->freeze_ends_at !== null;
    }

    private function assertCurrentCommunity(Expedition $challenge): void
    {
        $brand = $this->context->current();

        if ($brand && $challenge->brand_id !== $brand->id) {
            throw new AuthorizationException('Challenge does not belong to the current community.');
        }
    }
}
