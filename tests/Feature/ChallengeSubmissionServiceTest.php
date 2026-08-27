<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ChallengeTask;
use App\Models\ChallengeTaskCompletion;
use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\User;
use App\Livewire\ChallengeDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Learning\Application\ChallengeSubmissionOutcome;
use Modules\Learning\Application\ChallengeSubmissionService;
use Tests\TestCase;

final class ChallengeSubmissionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_creates_one_pending_completion_for_an_unlocked_task(): void
    {
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
