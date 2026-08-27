<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\ChallengeDetail;
use App\Models\ChallengeTask;
use App\Models\ChallengeTaskCompletion;
use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Modules\Learning\Application\ChallengeSubmissionOutcome;
use Modules\Learning\Application\ChallengeSubmissionService;
use Modules\Learning\Domain\Events\ChallengeSubmissionCreated;
use Tests\TestCase;

final class ChallengeSubmissionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_creates_one_pending_completion_for_an_unlocked_task(): void
    {
        Event::fake([ChallengeSubmissionCreated::class]);
        [$challenge, $learner] = $this->enrolledChallenge('Submission Challenge');
        $task = $this->task($challenge, 1);
        $service = app(ChallengeSubmissionService::class);

        $result = $service->submit($challenge, $task->id, $learner, 'Revit screenshot.', ['checklist' => ['done']]);

        self::assertSame(ChallengeSubmissionOutcome::Submitted, $result->outcome);
        self::assertFalse($result->isLate);
        $this->assertDatabaseHas('challenge_task_completions', [
            'challenge_task_id' => $task->id,
            'user_id' => $learner->id,
            'status' => 'pending',
            'evidence' => 'Revit screenshot.',
        ]);
        self::assertSame(
            ChallengeSubmissionOutcome::AlreadySubmitted,
            $service->submit($challenge, $task->id, $learner, 'Second screenshot.', null)->outcome,
        );
        Event::assertDispatched(ChallengeSubmissionCreated::class, function (ChallengeSubmissionCreated $event) use ($challenge, $learner, $task): bool {
            return $event->challengeId === $challenge->id
                && $event->taskId === $task->id
                && $event->learnerId === $learner->id
                && ! $event->isContest
                && ! $event->isLate;
        });
    }

    public function test_rejected_main_submission_can_be_resubmitted_without_creating_a_second_row(): void
    {
        [$challenge, $learner] = $this->enrolledChallenge('Resubmission Challenge');
        $task = $this->task($challenge, 1);
        $completion = ChallengeTaskCompletion::create([
            'challenge_task_id' => $task->id,
            'user_id' => $learner->id,
            'evidence' => 'Old evidence.',
            'status' => 'rejected',
            'reject_count' => 1,
            'review_note' => 'Add a test case.',
        ]);

        $result = app(ChallengeSubmissionService::class)->resubmit(
            $challenge,
            $task->id,
            $learner,
            'Updated evidence.',
            ['reflection' => ['verification' => 'Checked in Revit.']],
        );

        self::assertSame(ChallengeSubmissionOutcome::Resubmitted, $result->outcome);
        self::assertSame(1, ChallengeTaskCompletion::query()->count());
        self::assertSame('pending', $completion->fresh()->status);
        self::assertSame('Updated evidence.', $completion->fresh()->evidence);
        self::assertNull($completion->fresh()->review_note);
    }

    public function test_submission_is_blocked_before_the_task_is_unlocked(): void
    {
        [$challenge, $learner] = $this->enrolledChallenge('Locked Day Challenge');
        $task = $this->task($challenge, 7);

        $result = app(ChallengeSubmissionService::class)->submit($challenge, $task->id, $learner, 'Too early.', null);

        self::assertSame(ChallengeSubmissionOutcome::NotUnlocked, $result->outcome);
        $this->assertDatabaseMissing('challenge_task_completions', ['challenge_task_id' => $task->id]);
    }

    public function test_contest_entry_requires_approved_main_submission_and_reuses_a_rejected_entry(): void
    {
        Event::fake([ChallengeSubmissionCreated::class]);
        [$challenge, $learner] = $this->enrolledChallenge('Contest Submission Challenge');
        $task = ChallengeTask::create([
            'expedition_id' => $challenge->id,
            'day_number' => 1,
            'title' => 'Contest task',
            'is_contest' => true,
        ]);
        ChallengeTaskCompletion::create([
            'challenge_task_id' => $task->id,
            'user_id' => $learner->id,
            'evidence' => 'Approved main submission.',
            'status' => 'approved',
        ]);
        $rejectedEntry = ChallengeTaskCompletion::create([
            'challenge_task_id' => $task->id,
            'user_id' => $learner->id,
            'evidence' => 'Rejected entry.',
            'status' => 'rejected',
        ]);
        $service = app(ChallengeSubmissionService::class);

        $result = $service->submitContestEntry($challenge, $task->id, $learner, 'Replacement contest entry.');

        self::assertSame(ChallengeSubmissionOutcome::ContestSubmitted, $result->outcome);
        self::assertSame(2, ChallengeTaskCompletion::query()->count());
        self::assertSame('pending', $rejectedEntry->fresh()->status);
        self::assertSame('Replacement contest entry.', $rejectedEntry->fresh()->evidence);
        self::assertSame(
            ChallengeSubmissionOutcome::ContestEntryPending,
            $service->submitContestEntry($challenge, $task->id, $learner, 'Duplicate entry.')->outcome,
        );
        Event::assertDispatched(ChallengeSubmissionCreated::class, function (ChallengeSubmissionCreated $event) use ($challenge, $learner, $task): bool {
            return $event->challengeId === $challenge->id
                && $event->taskId === $task->id
                && $event->learnerId === $learner->id
                && $event->isContest
                && ! $event->isLate;
        });
    }

    public function test_challenge_detail_submits_through_the_submission_service(): void
    {
        [$challenge, $learner] = $this->enrolledChallenge('Livewire Submission Challenge');
        $task = $this->task($challenge, 1);

        Livewire::actingAs($learner)
            ->test(ChallengeDetail::class, ['slug' => $challenge->slug])
            ->set('taskEvidence.'.$task->id, 'Evidence submitted from the learner workspace.')
            ->call('completeTask', $task->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('challenge_task_completions', [
            'challenge_task_id' => $task->id,
            'user_id' => $learner->id,
            'status' => 'pending',
        ]);
    }

    public function test_challenge_detail_submits_a_contest_entry_through_the_submission_service(): void
    {
        [$challenge, $learner] = $this->enrolledChallenge('Livewire Contest Submission Challenge');
        $task = ChallengeTask::create([
            'expedition_id' => $challenge->id,
            'day_number' => 1,
            'title' => 'Contest task',
            'is_contest' => true,
        ]);
        ChallengeTaskCompletion::create([
            'challenge_task_id' => $task->id,
            'user_id' => $learner->id,
            'evidence' => 'Approved main submission.',
            'status' => 'approved',
        ]);

        Livewire::actingAs($learner)
            ->test(ChallengeDetail::class, ['slug' => $challenge->slug])
            ->set('taskEvidence.'.$task->id, 'Contest entry from the learner workspace.')
            ->call('submitMiniGame', $task->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('challenge_task_completions', [
            'challenge_task_id' => $task->id,
            'user_id' => $learner->id,
            'evidence' => 'Contest entry from the learner workspace.',
            'status' => 'pending',
        ]);
    }

    /** @return array{0: Expedition, 1: User} */
    private function enrolledChallenge(string $title): array
    {
        $owner = User::factory()->create();
        $learner = User::factory()->create();
        $challenge = Expedition::create([
            'title' => $title,
            'slug' => str($title)->slug()->toString(),
            'description' => 'Challenge submission service test.',
            'difficulty' => 'normal',
            'required_days' => 7,
            'max_members' => 50,
            'created_by' => $owner->id,
            'leader_id' => $owner->id,
            'status' => 'active',
        ]);
        ExpeditionMember::create([
            'brand_id' => $challenge->brand_id,
            'expedition_id' => $challenge->id,
            'user_id' => $learner->id,
            'joined_at' => now()->subHour(),
            'personal_starts_at' => now()->subHour(),
            'status' => 'approved',
        ]);

        return [$challenge, $learner];
    }

    private function task(Expedition $challenge, int $day): ChallengeTask
    {
        return ChallengeTask::create([
            'expedition_id' => $challenge->id,
            'day_number' => $day,
            'title' => 'Task '.$day,
        ]);
    }
}
