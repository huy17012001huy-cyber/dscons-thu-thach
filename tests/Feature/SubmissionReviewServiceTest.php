<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\ChallengeDetail;
use App\Models\ChallengeSubmissionXpAward;
use App\Models\ChallengeTask;
use App\Models\ChallengeTaskCompletion;
use App\Models\Expedition;
use App\Models\User;
use App\Models\XpTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Learning\Application\SubmissionReviewService;
use Modules\Learning\Domain\Events\ChallengeSubmissionReviewed;
use Modules\Learning\Domain\Listeners\HandleChallengeSubmissionReviewed;
use Tests\TestCase;

final class SubmissionReviewServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_admin_can_approve_a_pending_submission_once(): void
    {
        $admin = $this->communityAdmin();
        $learner = User::factory()->create();
        $challenge = $this->challenge($admin, 'Review Service Challenge');
        $completion = $this->completion($challenge, $learner);
        $service = app(SubmissionReviewService::class);

        self::assertNull($service->approve($challenge, $completion->id, $learner, 70, null));

        $result = $service->approve($challenge, $completion->id, $admin, 120, ['evidence' => 40]);

        self::assertNotNull($result);
        self::assertTrue($result->shouldAwardXp);
        self::assertSame('approved', $result->completion->status);
        self::assertSame(100, $result->completion->score);
        $this->assertDatabaseHas('challenge_submission_xp_awards', [
            'challenge_task_completion_id' => $completion->id,
            'user_id' => $learner->id,
        ]);
        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $learner->id,
            'type' => 'expedition_checkin',
        ]);
        app(HandleChallengeSubmissionReviewed::class)->handle(new ChallengeSubmissionReviewed(
            $challenge,
            $completion,
            true,
            'approved',
            null,
        ));
        self::assertSame(1, ChallengeSubmissionXpAward::query()->count());
        self::assertSame(1, XpTransaction::query()->where('type', 'expedition_checkin')->count());
        $this->assertDatabaseHas('challenge_task_reviews', [
            'completion_id' => $completion->id,
            'reviewer_id' => $admin->id,
            'status' => 'approved',
            'score' => 100,
        ]);
        self::assertNull($service->approve($challenge, $completion->id, $admin, 70, null));

        $rejected = $service->reject($challenge, $completion->id, $admin, 'Replace this review.', 70, null);
        self::assertNotNull($rejected);
        self::assertFalse($rejected->shouldAwardXp);
        self::assertSame('rejected', $rejected->completion->status);
    }

    public function test_rejection_tracks_primary_submission_without_penalizing_a_contest_entry(): void
    {
        $admin = $this->communityAdmin();
        $learner = User::factory()->create();
        $challenge = $this->challenge($admin, 'Contest Review Challenge');
        $task = ChallengeTask::create([
            'expedition_id' => $challenge->id,
            'day_number' => 1,
            'title' => 'Contest task',
            'is_contest' => true,
        ]);
        $primary = $this->completion($challenge, $learner, $task);
        $entry = $this->completion($challenge, $learner, $task);
        $service = app(SubmissionReviewService::class);

        $primaryResult = $service->reject($challenge, $primary->id, $admin, 'Need real Revit evidence.', -5, null);
        $entryResult = $service->reject($challenge, $entry->id, $admin, 'Need a clearer demo.', 60, null);

        self::assertNotNull($primaryResult);
        self::assertSame(1, $primary->fresh()->reject_count);
        self::assertNotNull($entryResult);
        self::assertSame(0, $entry->fresh()->reject_count);
        self::assertSame(60, $entryResult->completion->score);
    }

    public function test_review_cannot_target_a_submission_from_another_challenge(): void
    {
        $admin = $this->communityAdmin();
        $learner = User::factory()->create();
        $challenge = $this->challenge($admin, 'Current Challenge');
        $otherChallenge = $this->challenge($admin, 'Other Challenge');
        $otherCompletion = $this->completion($otherChallenge, $learner);

        self::assertNull(
            app(SubmissionReviewService::class)->approve($challenge, $otherCompletion->id, $admin, 70, null),
        );
        self::assertSame('pending', $otherCompletion->fresh()->status);
    }

    public function test_community_admin_can_approve_all_pending_submissions_without_duplicate_xp_pairs(): void
    {
        $admin = $this->communityAdmin();
        $challenge = $this->challenge($admin, 'Bulk Review Challenge');
        $task = ChallengeTask::create([
            'expedition_id' => $challenge->id,
            'day_number' => 1,
            'title' => 'Bulk review task',
        ]);
        $newLearner = User::factory()->create();
        $returningLearner = User::factory()->create();
        $newCompletion = $this->completion($challenge, $newLearner, $task);
        ChallengeTaskCompletion::create([
            'challenge_task_id' => $task->id,
            'user_id' => $returningLearner->id,
            'evidence' => 'Previously approved evidence.',
            'status' => 'approved',
        ]);
        $returningCompletion = $this->completion($challenge, $returningLearner, $task);

        $result = app(SubmissionReviewService::class)->approveAllPending($challenge, $admin);

        self::assertNotNull($result);
        self::assertCount(2, $result->completions);
        self::assertCount(1, $result->awardableCompletions);
        self::assertSame($newCompletion->id, $result->awardableCompletions->first()->id);
        self::assertSame('approved', $newCompletion->fresh()->status);
        self::assertSame('approved', $returningCompletion->fresh()->status);
        $this->assertDatabaseCount('challenge_task_reviews', 2);
        self::assertSame(1, ChallengeSubmissionXpAward::query()->count());
        self::assertSame(1, XpTransaction::query()->where('type', 'expedition_checkin')->count());
    }

    public function test_challenge_detail_uses_the_review_service_for_an_admin_action(): void
    {
        $admin = $this->communityAdmin();
        $learner = User::factory()->create();
        $challenge = $this->challenge($admin, 'Livewire Review Challenge');
        $completion = $this->completion($challenge, $learner);

        Livewire::actingAs($admin)
            ->test(ChallengeDetail::class, ['slug' => $challenge->slug])
            ->set('reviewScores.'.$completion->id, 85)
            ->call('approveSubmission', $completion->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('challenge_task_completions', [
            'id' => $completion->id,
            'status' => 'approved',
            'score' => 85,
        ]);
    }

    public function test_challenge_detail_approves_all_pending_submissions_through_the_review_service(): void
    {
        $admin = $this->communityAdmin();
        $learner = User::factory()->create();
        $challenge = $this->challenge($admin, 'Livewire Bulk Review Challenge');
        $completion = $this->completion($challenge, $learner);

        Livewire::actingAs($admin)
            ->test(ChallengeDetail::class, ['slug' => $challenge->slug])
            ->call('approveAllPending')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('challenge_task_completions', [
            'id' => $completion->id,
            'status' => 'approved',
            'score' => 70,
        ]);
    }

    private function communityAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->brandRoles()->attach(brand()->id, ['role' => 'admin']);

        return $admin;
    }

    private function challenge(User $owner, string $title): Expedition
    {
        return Expedition::create([
            'title' => $title,
            'slug' => str($title)->slug()->toString(),
            'description' => 'Submission review service test.',
            'difficulty' => 'normal',
            'required_days' => 7,
            'max_members' => 50,
            'created_by' => $owner->id,
            'leader_id' => $owner->id,
            'status' => 'active',
        ]);
    }

    private function completion(
        Expedition $challenge,
        User $learner,
        ?ChallengeTask $task = null,
    ): ChallengeTaskCompletion {
        $task ??= ChallengeTask::create([
            'expedition_id' => $challenge->id,
            'day_number' => 1,
            'title' => 'Review task '.str()->random(6),
        ]);

        return ChallengeTaskCompletion::create([
            'challenge_task_id' => $task->id,
            'user_id' => $learner->id,
            'evidence' => 'Screenshot from Revit.',
            'status' => 'pending',
        ]);
    }
}
