<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\ChallengeDetail;
use App\Models\Expedition;
use App\Models\ExpeditionCheckin;
use App\Models\ExpeditionMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Learning\Application\ChallengeCheckinOutcome;
use Modules\Learning\Application\ChallengeCheckinService;
use Tests\TestCase;

final class ChallengeCheckinServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_member_can_check_in_once_and_earn_xp(): void
    {
        [$challenge, $learner] = $this->enrolledChallenge();
        $service = app(ChallengeCheckinService::class);

        self::assertSame(
            ChallengeCheckinOutcome::CheckedIn,
            $service->checkin($challenge, $learner, 'Completed the Revit practice and checked the result.'),
        );
        self::assertSame(
            ChallengeCheckinOutcome::AlreadyCheckedIn,
            $service->checkin($challenge, $learner, 'Trying to check in a second time.'),
        );
        self::assertSame(1, ExpeditionCheckin::query()->count());
        self::assertGreaterThan(0, $learner->fresh()->xp);
        self::assertNotNull(ExpeditionMember::query()->firstOrFail()->fresh()->last_checkin_at);
    }

    public function test_unenrolled_member_cannot_check_in(): void
    {
        [$challenge] = $this->enrolledChallenge();
        $unenrolled = User::factory()->create();

        self::assertSame(
            ChallengeCheckinOutcome::NotEnrolled,
            app(ChallengeCheckinService::class)->checkin($challenge, $unenrolled, 'Trying to check in.'),
        );
        $this->assertDatabaseCount('expedition_checkins', 0);
    }

    public function test_challenge_detail_uses_the_checkin_service(): void
    {
        [$challenge, $learner] = $this->enrolledChallenge('Livewire Checkin Challenge');

        Livewire::actingAs($learner)
            ->test(ChallengeDetail::class, ['slug' => $challenge->slug])
            ->set('checkinContent', 'Finished the scheduled work and verified it in the project model.')
            ->call('checkin')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('expedition_checkins', [
            'expedition_id' => $challenge->id,
            'user_id' => $learner->id,
        ]);
    }

    /** @return array{0: Expedition, 1: User} */
    private function enrolledChallenge(string $title = 'Checkin Service Challenge'): array
    {
        $owner = User::factory()->create();
        $learner = User::factory()->create();
        $challenge = Expedition::create([
            'title' => $title,
            'slug' => str($title)->slug()->toString(),
            'description' => 'Challenge checkin service test.',
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
            'joined_at' => now(),
            'personal_starts_at' => now(),
            'status' => 'approved',
        ]);

        return [$challenge, $learner];
    }
}
