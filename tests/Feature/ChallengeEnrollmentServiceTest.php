<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Learning\Application\ChallengeEnrollmentService;
use Tests\TestCase;

final class ChallengeEnrollmentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_challenge_request_is_pending_and_cannot_be_duplicated(): void
    {
        $user = User::factory()->create(['source' => null]);
        $challenge = $this->challenge($user);
        $service = app(ChallengeEnrollmentService::class);

        self::assertSame(ChallengeEnrollmentService::PENDING_REVIEW, $service->request($challenge, $user));
        self::assertSame(ChallengeEnrollmentService::DUPLICATE, $service->request($challenge, $user));
        $this->assertDatabaseHas('expedition_members', [
            'expedition_id' => $challenge->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    public function test_webhook_account_is_auto_approved(): void
    {
        $user = User::factory()->create(['source' => 'webhook']);
        $challenge = $this->challenge($user, 'Webhook Challenge');

        self::assertSame(
            ChallengeEnrollmentService::AUTO_APPROVED,
            app(ChallengeEnrollmentService::class)->request($challenge, $user),
        );
        $member = ExpeditionMember::query()->firstOrFail();
        self::assertSame('approved', $member->status);
        self::assertNotNull($member->approved_at);
    }

    public function test_paid_challenge_request_waits_for_payment_without_notifying_for_review(): void
    {
        $user = User::factory()->create(['source' => null]);
        $challenge = $this->challenge($user, 'Paid Challenge');
        $challenge->update(['price' => 350000]);

        self::assertSame(
            ChallengeEnrollmentService::PENDING_PAYMENT,
            app(ChallengeEnrollmentService::class)->request($challenge, $user),
        );
        $this->assertDatabaseHas('expedition_members', [
            'expedition_id' => $challenge->id,
            'user_id' => $user->id,
            'status' => 'pending_payment',
            'payment_amount' => 350000,
        ]);
    }

    public function test_pending_enrollment_can_be_cancelled_and_approved_enrollment_can_start(): void
    {
        $user = User::factory()->create(['source' => null]);
        $challenge = $this->challenge($user);
        $service = app(ChallengeEnrollmentService::class);

        $service->request($challenge, $user);
        self::assertTrue($service->cancel($challenge, $user));
        self::assertFalse($service->cancel($challenge, $user));

        ExpeditionMember::create([
            'expedition_id' => $challenge->id,
            'user_id' => $user->id,
            'joined_at' => now(),
            'status' => 'approved',
        ]);

        self::assertTrue($service->start($challenge, $user));
        self::assertFalse($service->start($challenge, $user));
        self::assertNotNull(ExpeditionMember::query()->value('personal_starts_at'));
    }

    private function challenge(User $owner, string $title = 'Service Challenge'): Expedition
    {
        return Expedition::create([
            'title' => $title,
            'slug' => str($title)->slug()->toString(),
            'description' => 'Challenge enrollment service test.',
            'difficulty' => 'normal',
            'required_days' => 7,
            'max_members' => 50,
            'created_by' => $owner->id,
            'leader_id' => $owner->id,
            'status' => 'active',
            'price' => 0,
        ]);
    }
}
