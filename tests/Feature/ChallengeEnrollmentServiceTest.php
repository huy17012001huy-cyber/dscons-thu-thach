<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\CommunityContext;
use App\Models\Brand;
use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
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

    public function test_pending_enrollment_notifies_only_the_challenge_community_admins_and_super_admins(): void
    {
        $communityAdmin = User::factory()->create();
        $communityAdmin->brandRoles()->attach(brand()->id, ['role' => 'admin']);
        $superAdmin = User::factory()->create(['is_admin' => true]);
        $otherBrand = $this->otherBrand();
        $otherAdmin = User::factory()->create();
        $otherAdmin->brandRoles()->attach($otherBrand->id, ['role' => 'admin']);
        $learner = User::factory()->create(['source' => null]);
        $challenge = $this->challenge($communityAdmin, 'Notification Challenge');

        self::assertSame(ChallengeEnrollmentService::PENDING_REVIEW, app(ChallengeEnrollmentService::class)->request($challenge, $learner));

        $this->assertDatabaseHas('notifications', ['notifiable_id' => $communityAdmin->id]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $superAdmin->id]);
        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $otherAdmin->id]);
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

    public function test_only_a_community_admin_can_review_a_pending_enrollment(): void
    {
        $admin = User::factory()->create();
        $admin->brandRoles()->attach(brand()->id, ['role' => 'admin']);
        $learner = User::factory()->create(['source' => null]);
        $challenge = $this->challenge($admin, 'Review Challenge');
        $service = app(ChallengeEnrollmentService::class);

        $service->request($challenge, $learner);
        $member = ExpeditionMember::query()->firstOrFail();

        self::assertNull($service->approve($challenge, $member->id, $learner));
        self::assertSame('pending', $member->fresh()->status);

        $approved = $service->approve($challenge, $member->id, $admin);
        self::assertNotNull($approved);
        self::assertSame('approved', $approved->status);
        self::assertSame($admin->id, $approved->approved_by);

        $secondLearner = User::factory()->create(['source' => null]);
        $service->request($challenge, $secondLearner);
        $secondMember = ExpeditionMember::query()->where('user_id', $secondLearner->id)->firstOrFail();
        $rejected = $service->reject(
            $challenge,
            $secondMember->id,
            $admin,
        );
        self::assertNotNull($rejected);
        self::assertSame('rejected', $rejected->status);
    }

    public function test_enrollment_service_rejects_challenges_outside_the_current_community(): void
    {
        $admin = User::factory()->create();
        $admin->brandRoles()->attach(brand()->id, ['role' => 'admin']);
        $learner = User::factory()->create(['source' => null]);
        $challenge = $this->challenge($admin, 'Scoped Review Challenge');
        $service = app(ChallengeEnrollmentService::class);
        $service->request($challenge, $learner);
        $member = ExpeditionMember::query()->firstOrFail();
        $otherBrand = Brand::create([
            'name' => 'Other community',
            'slug' => 'other-community',
            'domain' => 'other-community.test',
            'status' => 'active',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#E1F4F7',
            'theme_bg' => '#F7FAFC',
        ]);

        $this->expectException(AuthorizationException::class);

        app(CommunityContext::class)->run($otherBrand, function () use ($service, $challenge, $member, $admin): void {
            $service->approve($challenge, $member->id, $admin);
        });
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

    private function otherBrand(): Brand
    {
        return Brand::create([
            'name' => 'Other Community',
            'slug' => 'other-community-notification',
            'domain' => 'other-community-notification.test',
            'status' => 'active',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#E1F4F7',
            'theme_bg' => '#F7FAFC',
        ]);
    }
}
