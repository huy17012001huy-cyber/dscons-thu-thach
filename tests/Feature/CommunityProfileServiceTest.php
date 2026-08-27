<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Community\Application\CommunityProfileData;
use Modules\Community\Application\CommunityProfileService;
use Tests\TestCase;

final class CommunityProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_admin_can_update_its_profile_and_guidance(): void
    {
        $admin = $this->admin();
        $community = app(CommunityProfileService::class)->update(brand(), $admin, $this->data('DSCons Updated'));

        self::assertSame('DSCons Updated', $community->name);
        $this->assertDatabaseHas('brands', [
            'id' => brand()->id,
            'name' => 'DSCons Updated',
            'guide_content' => 'Guide content',
            'rules_content' => 'Rules content',
        ]);
    }

    public function test_member_and_cross_community_admin_cannot_update_the_profile(): void
    {
        $member = User::factory()->create();
        $member->brandRoles()->attach(brand()->id, ['role' => 'member']);
        $service = app(CommunityProfileService::class);
        $deniedAttempts = 0;

        try {
            $service->update(brand(), $member, $this->data('No access'));
            self::fail('A member must not update community settings.');
        } catch (AuthorizationException) {
            $deniedAttempts++;
        }

        $otherBrand = $this->otherBrand();
        $otherAdmin = User::factory()->create();
        $otherAdmin->brandRoles()->attach($otherBrand->id, ['role' => 'admin']);
        try {
            $service->update($otherBrand, $otherAdmin, $this->data('Other community'));
            self::fail('An admin must not update a community outside the current context.');
        } catch (AuthorizationException) {
            $deniedAttempts++;
        }

        self::assertSame(2, $deniedAttempts);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->brandRoles()->attach(brand()->id, ['role' => 'admin']);

        return $admin;
    }

    private function data(string $name): CommunityProfileData
    {
        return new CommunityProfileData(
            name: $name,
            tagline: 'A focused engineering community',
            description: 'Community profile description',
            guideContent: 'Guide content',
            rulesContent: 'Rules content',
            logoPath: 'community/logos/new.png',
            bannerPath: 'community/banners/new.png',
        );
    }

    private function otherBrand(): Brand
    {
        return Brand::create([
            'name' => 'Other Profile Community',
            'slug' => 'other-profile-community',
            'domain' => 'other-profile-community.test',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#DCECF7',
            'theme_bg' => '#F8FAFC',
            'registration_mode' => 'invite',
            'is_invite_only' => true,
        ]);
    }
}
