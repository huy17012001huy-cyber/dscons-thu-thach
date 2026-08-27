<?php

namespace Tests\Feature;

use App\Livewire\CommunityMembers;
use App\Models\Brand;
use App\Models\EngineerCv;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_can_access_global_admin_routes(): void
    {
        $communityAdmin = User::factory()->create();
        $communityAdmin->brandRoles()->attach(brand()->id, ['role' => 'admin']);

        $this->actingAs($communityAdmin)
            ->get(route('admin.users'))
            ->assertForbidden();

        $superAdmin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($superAdmin)
            ->get(route('admin.users'))
            ->assertOk();
    }

    public function test_community_admin_is_scoped_to_its_community(): void
    {
        $other = $this->makeBrand('other-community');
        $admin = User::factory()->create();
        $admin->brandRoles()->attach(brand()->id, ['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('community.manage', ['community' => brand()->slug]))
            ->assertOk();

        $this->get(route('community.manage', ['community' => $other->slug]))
            ->assertForbidden();
    }

    public function test_moderator_can_review_reports_but_cannot_change_settings(): void
    {
        $moderator = User::factory()->create();
        $moderator->brandRoles()->attach(brand()->id, ['role' => 'moderator']);

        $this->actingAs($moderator)
            ->get(route('community.manage.moderation', ['community' => brand()->slug]))
            ->assertOk();

        $this->get(route('community.manage.settings', ['community' => brand()->slug]))
            ->assertForbidden();
    }

    public function test_owner_can_assign_moderator_and_admin_cannot_assign_admin(): void
    {
        $owner = User::factory()->create();
        brand()->update(['owner_id' => $owner->id]);
        $owner->brandRoles()->attach(brand()->id, ['role' => 'owner']);
        $member = User::factory()->create();
        $member->brandRoles()->attach(brand()->id, ['role' => 'member']);

        Livewire::actingAs($owner)
            ->test(CommunityMembers::class)
            ->call('changeRole', $member->id, 'moderator');

        $this->assertDatabaseHas('brand_user', [
            'brand_id' => brand()->id,
            'user_id' => $member->id,
            'role' => 'moderator',
        ]);

        $admin = User::factory()->create();
        $admin->brandRoles()->attach(brand()->id, ['role' => 'admin']);
        $secondMember = User::factory()->create();
        $secondMember->brandRoles()->attach(brand()->id, ['role' => 'member']);

        Livewire::actingAs($admin)
            ->test(CommunityMembers::class)
            ->call('changeRole', $secondMember->id, 'admin')
            ->assertStatus(403);
    }

    public function test_only_owner_can_transfer_community_ownership(): void
    {
        $owner = User::factory()->create();
        brand()->update(['owner_id' => $owner->id]);
        $owner->brandRoles()->attach(brand()->id, ['role' => 'owner']);
        $member = User::factory()->create();
        $member->brandRoles()->attach(brand()->id, ['role' => 'member']);

        Livewire::actingAs($owner)
            ->test(CommunityMembers::class)
            ->call('transferOwnership', $member->id);

        $this->assertDatabaseHas('brands', ['id' => brand()->id, 'owner_id' => $member->id]);
        $this->assertDatabaseHas('brand_user', ['brand_id' => brand()->id, 'user_id' => $owner->id, 'role' => 'admin']);
        $this->assertDatabaseHas('brand_user', ['brand_id' => brand()->id, 'user_id' => $member->id, 'role' => 'owner']);
    }

    public function test_membership_is_not_changed_when_community_role_changes(): void
    {
        $member = User::factory()->create();
        $member->brandRoles()->attach(brand()->id, ['role' => 'member']);

        $this->assertFalse($member->hasPremiumMembership(brand()->id));
        $this->assertTrue($member->isCommunityParticipant(brand()->id));
        $this->assertFalse($member->isCommunityAdmin(brand()->id));
    }

    public function test_super_admin_recruitment_screen_can_view_cv_from_all_communities(): void
    {
        $other = $this->makeBrand('talent-community');
        $engineer = User::factory()->create(['account_type' => 'engineer']);
        EngineerCv::create([
            'brand_id' => $other->id,
            'user_id' => $engineer->id,
            'title' => 'CV ngoài DSCons',
            'status' => 'published',
            'data' => ['headline' => 'BIM Coordinator'],
        ]);
        $superAdmin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($superAdmin)
            ->get(route('admin.recruitment'))
            ->assertOk()
            ->assertSee('CV ngoài DSCons');
    }

    private function makeBrand(string $slug): Brand
    {
        return Brand::create([
            'name' => $slug,
            'slug' => $slug,
            'domain' => $slug.'.test',
            'status' => 'active',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#E1F4F7',
            'theme_bg' => '#F7FAFC',
        ]);
    }
}
