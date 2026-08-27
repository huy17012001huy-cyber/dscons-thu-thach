<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CommunityApplication;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Community\Application\CommunityApplicationService;
use Tests\TestCase;

final class CommunityApplicationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_approval_creates_a_complete_community_once(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $application = $this->application();

        $brand = app(CommunityApplicationService::class)->approve($application->id, $admin, 'Approved for launch.');

        self::assertNotNull($brand);
        self::assertSame($application->applicant_id, $brand->owner_id);
        $this->assertDatabaseHas('brand_user', ['brand_id' => $brand->id, 'user_id' => $application->applicant_id, 'role' => 'owner']);
        self::assertTrue(Membership::withoutGlobalScopes()->where('brand_id', $brand->id)->where('user_id', $application->applicant_id)->exists());
        self::assertSame(2, MembershipPlan::withoutGlobalScopes()->where('brand_id', $brand->id)->count());
        self::assertSame('approved', $application->fresh()->status);
        self::assertNull(app(CommunityApplicationService::class)->approve($application->id, $admin, null));
    }

    public function test_non_super_admin_cannot_review_a_community_application(): void
    {
        $this->expectException(AuthorizationException::class);

        app(CommunityApplicationService::class)->reject($this->application()->id, User::factory()->create(), null);
    }

    private function application(): CommunityApplication
    {
        return CommunityApplication::create([
            'applicant_id' => User::factory()->create()->id,
            'name' => 'Testing Community',
            'slug' => 'testing-community',
            'tagline' => 'A test community.',
            'description' => 'A full test community application.',
            'proposed_premium_price' => 199000,
        ]);
    }
}
