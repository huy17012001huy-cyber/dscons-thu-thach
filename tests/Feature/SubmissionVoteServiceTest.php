<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\ChallengeDetail;
use App\Models\ChallengeTask;
use App\Models\ChallengeTaskCompletion;
use App\Models\Expedition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Learning\Application\SubmissionVoteService;
use Tests\TestCase;

final class SubmissionVoteServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_admin_can_toggle_a_vote_for_a_submission_in_its_challenge(): void
    {
        $admin = $this->admin();
        [$challenge, $completion] = $this->challengeWithCompletion($admin, 'Vote Service Challenge');
        $service = app(SubmissionVoteService::class);

        self::assertTrue($service->toggle($challenge, $completion->id, $admin, 'good'));
        $this->assertDatabaseHas('submission_votes', [
            'completion_id' => $completion->id,
            'user_id' => $admin->id,
            'vote_type' => 'good',
        ]);
        self::assertTrue($service->toggle($challenge, $completion->id, $admin, 'good'));
        $this->assertDatabaseMissing('submission_votes', ['completion_id' => $completion->id, 'user_id' => $admin->id]);
    }

    public function test_vote_cannot_target_a_submission_from_another_challenge(): void
    {
        $admin = $this->admin();
        [$challenge] = $this->challengeWithCompletion($admin, 'Current Vote Challenge');
        [, $otherCompletion] = $this->challengeWithCompletion($admin, 'Other Vote Challenge');

        self::assertFalse(app(SubmissionVoteService::class)->toggle($challenge, $otherCompletion->id, $admin, 'good'));
        $this->assertDatabaseMissing('submission_votes', ['completion_id' => $otherCompletion->id]);
    }

    public function test_challenge_detail_toggles_votes_through_the_service(): void
    {
        $admin = $this->admin();
        [$challenge, $completion] = $this->challengeWithCompletion($admin, 'Livewire Vote Challenge');

        Livewire::actingAs($admin)
            ->test(ChallengeDetail::class, ['slug' => $challenge->slug])
            ->call('toggleVote', $completion->id, 'good')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('submission_votes', [
            'completion_id' => $completion->id,
            'user_id' => $admin->id,
            'vote_type' => 'good',
        ]);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->brandRoles()->attach(brand()->id, ['role' => 'admin']);

        return $admin;
    }

    /** @return array{0: Expedition, 1: ChallengeTaskCompletion} */
    private function challengeWithCompletion(User $owner, string $title): array
    {
        $learner = User::factory()->create();
        $challenge = Expedition::create([
            'title' => $title,
            'slug' => str($title)->slug()->toString(),
            'description' => 'Submission vote service test.',
            'difficulty' => 'normal',
            'required_days' => 7,
            'max_members' => 50,
            'created_by' => $owner->id,
            'leader_id' => $owner->id,
            'status' => 'active',
        ]);
        $task = ChallengeTask::create([
            'expedition_id' => $challenge->id,
            'day_number' => 1,
            'title' => 'Vote task',
        ]);
        $completion = ChallengeTaskCompletion::create([
            'challenge_task_id' => $task->id,
            'user_id' => $learner->id,
            'evidence' => 'Revit proof.',
            'status' => 'pending',
        ]);

        return [$challenge, $completion];
    }
}
