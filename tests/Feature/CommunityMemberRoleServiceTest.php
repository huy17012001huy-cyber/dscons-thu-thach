<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\CommunityContext;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Community\Application\CommunityMemberRoleService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class CommunityMemberRoleServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_promote_a_member_and_an_audit_record_is_created(): void
    {
        $owner = $this->userWithRole('owner');
        brand()->update(['owner_id' => $owner->id]);
        $member = $this->userWithRole('member');

        app(CommunityMemberRoleService::class)->changeRole(brand(), $owner, $member->id, 'moderator');

        $this->assertDatabaseHas('brand_user', ['brand_id' => brand()->id, 'user_id' => $member->id, 'role' => 'moderator']);
        $this->assertDatabaseHas('community_role_audits', [
            'brand_id' => brand()->id,
            'actor_id' => $owner->id,
            'user_id' => $member->id,
            'from_role' => 'member',
            'to_role' => 'moderator',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'brand_id' => brand()->id,
            'actor_id' => $owner->id,
            'domain' => 'community',
            'action' => 'member_role_changed',
            'subject_type' => User::class,
            'subject_id' => $member->id,
        ]);
    }

    public function test_admin_cannot_manage_another_community_or_promote_admins(): void
    {
        $admin = $this->userWithRole('admin');
        $member = $this->userWithRole('member');
        $otherBrand = $this->otherBrand();
        $member->brandRoles()->attach($otherBrand->id, ['role' => 'member']);
        $service = app(CommunityMemberRoleService::class);

        try {
            $service->changeRole(brand(), $admin, $member->id, 'admin');
            self::fail('A community admin must not be able to promote an admin.');
        } catch (HttpException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }

        try {
            $service->changeRole($otherBrand, $admin, $member->id, 'moderator');
            self::fail('A community admin must not manage another community.');
        } catch (HttpException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_owner_can_transfer_ownership_to_a_member_of_the_same_community(): void
    {
        $owner = $this->userWithRole('owner');
        brand()->update(['owner_id' => $owner->id]);
        $member = $this->userWithRole('member');

        app(CommunityMemberRoleService::class)->transferOwnership(brand(), $owner, $member->id);

        $this->assertDatabaseHas('brands', ['id' => brand()->id, 'owner_id' => $member->id]);
        $this->assertDatabaseHas('brand_user', ['brand_id' => brand()->id, 'user_id' => $owner->id, 'role' => 'admin']);
        $this->assertDatabaseHas('brand_user', ['brand_id' => brand()->id, 'user_id' => $member->id, 'role' => 'owner']);
        $this->assertDatabaseCount('community_role_audits', 2);
        $this->assertDatabaseHas('audit_logs', [
            'brand_id' => brand()->id,
            'actor_id' => $owner->id,
            'domain' => 'community',
            'action' => 'ownership_transferred',
            'subject_type' => Brand::class,
            'subject_id' => brand()->id,
        ]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->brandRoles()->attach(app(CommunityContext::class)->require()->id, ['role' => $role]);

        return $user;
    }

    private function otherBrand(): Brand
    {
        return Brand::create([
            'name' => 'Other Role Community',
            'slug' => 'other-role-community',
            'domain' => 'other-role-community.test',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#DCECF7',
            'theme_bg' => '#F8FAFC',
            'registration_mode' => 'invite',
            'is_invite_only' => true,
        ]);
    }
}
