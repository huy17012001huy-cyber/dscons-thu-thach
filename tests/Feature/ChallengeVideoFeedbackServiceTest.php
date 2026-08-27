<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\ChallengeDetail;
use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Learning\Application\ChallengeVideoFeedbackOutcome;
use Modules\Learning\Application\ChallengeVideoFeedbackService;
use Tests\TestCase;

final class ChallengeVideoFeedbackServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_submit_and_community_admin_can_review_video_feedback(): void
    {
        [$challenge, $learner, $member] = $this->enrolledChallenge();
        $admin = User::factory()->create();
        $admin->brandRoles()->attach(brand()->id, ['role' => 'admin']);
        $service = app(ChallengeVideoFeedbackService::class);

        self::assertSame(
            ChallengeVideoFeedbackOutcome::Submitted,
            $service->submit($challenge, $learner, 'https://video.example.test/final-demo'),
        );
        self::assertSame('pending', $member->fresh()->video_feedback_status);
        $approved = $service->approve($challenge, $member->id, $admin);
        self::assertNotNull($approved);
        self::assertSame('approved', $approved->video_feedback_status);

        $service->submit($challenge, $learner, 'https://video.example.test/revised-demo');
        $rejected = $service->reject($challenge, $member->id, $admin, 'Please show the installed tool running.');
        self::assertNotNull($rejected);
        self::assertSame('rejected', $rejected->video_feedback_status);
        self::assertNull($rejected->video_feedback_url);
    }

    public function test_video_review_is_scoped_to_the_current_challenge(): void
    {
        [$challenge, $learner, $member] = $this->enrolledChallenge();
        $otherOwner = User::factory()->create();
        $otherChallenge = Expedition::create([
            'title' => 'Other video challenge',
            'slug' => 'other-video-challenge',
            'description' => 'Scoped review test.',
            'difficulty' => 'normal',
            'required_days' => 7,
            'max_members' => 50,
            'created_by' => $otherOwner->id,
            'leader_id' => $otherOwner->id,
            'status' => 'active',
        ]);
        $admin = User::factory()->create();
        $admin->brandRoles()->attach(brand()->id, ['role' => 'admin']);
        app(ChallengeVideoFeedbackService::class)->submit($challenge, $learner, 'https://video.example.test/pending');

        self::assertNull(app(ChallengeVideoFeedbackService::class)->approve($otherChallenge, $member->id, $admin));
        self::assertSame('pending', $member->fresh()->video_feedback_status);
    }

    public function test_challenge_detail_submits_video_feedback_through_the_service(): void
    {
        [$challenge, $learner] = $this->enrolledChallenge('Livewire Video Feedback Challenge');

        Livewire::actingAs($learner)
            ->test(ChallengeDetail::class, ['slug' => $challenge->slug])
            ->set('videoFeedbackUrl', 'https://video.example.test/livewire-demo')
            ->call('submitVideoFeedback')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('expedition_members', [
            'expedition_id' => $challenge->id,
            'user_id' => $learner->id,
            'video_feedback_status' => 'pending',
        ]);
    }

    /** @return array{0: Expedition, 1: User, 2: ExpeditionMember} */
    private function enrolledChallenge(string $title = 'Video Feedback Challenge'): array
    {
        $owner = User::factory()->create();
        $learner = User::factory()->create();
        $challenge = Expedition::create([
            'title' => $title,
            'slug' => str($title)->slug()->toString(),
            'description' => 'Video feedback service test.',
            'difficulty' => 'normal',
            'required_days' => 7,
            'max_members' => 50,
            'created_by' => $owner->id,
            'leader_id' => $owner->id,
            'status' => 'active',
        ]);
        $member = ExpeditionMember::create([
            'brand_id' => $challenge->brand_id,
            'expedition_id' => $challenge->id,
            'user_id' => $learner->id,
            'joined_at' => now(),
            'status' => 'approved',
        ]);

        return [$challenge, $learner, $member];
    }
}
