<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\EngineerCv;
use App\Models\EngineerProfile;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Community\Application\CommunityFeatureSettingsService;
use Tests\TestCase;

final class CommunityFeatureSettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabling_cv_hides_cv_and_engineer_profile_after_the_setting_commits(): void
    {
        $admin = $this->admin();
        $engineer = User::factory()->create(['account_type' => 'engineer']);
        EngineerCv::create([
            'brand_id' => brand()->id,
            'user_id' => $engineer->id,
            'title' => 'Published CV',
            'status' => 'published',
            'data' => [],
        ]);
        EngineerProfile::create([
            'brand_id' => brand()->id,
            'user_id' => $engineer->id,
            'anonymized_code' => 'BIM-0001',
            'is_searchable' => true,
        ]);

        $updated = app(CommunityFeatureSettingsService::class)->update(brand(), $admin, false, false);

        self::assertFalse($updated->has_cv);
        self::assertFalse($updated->has_recruitment);
        $this->assertDatabaseHas('engineer_cvs', ['brand_id' => brand()->id, 'user_id' => $engineer->id, 'status' => 'draft']);
        $this->assertDatabaseHas('engineer_profiles', ['brand_id' => brand()->id, 'user_id' => $engineer->id, 'is_searchable' => false]);
    }

    public function test_member_and_admin_from_another_community_cannot_update_settings(): void
    {
        $member = User::factory()->create();
        $member->brandRoles()->attach(brand()->id, ['role' => 'member']);
        $service = app(CommunityFeatureSettingsService::class);
        $deniedAttempts = 0;

        try {
            $service->update(brand(), $member, false, false);
            self::fail('A member must not update feature settings.');
        } catch (AuthorizationException) {
            $deniedAttempts++;
        }

        $otherBrand = $this->otherBrand();
        $otherAdmin = User::factory()->create();
        $otherAdmin->brandRoles()->attach($otherBrand->id, ['role' => 'admin']);
        try {
            $service->update($otherBrand, $otherAdmin, true, true);
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

    private function otherBrand(): Brand
    {
        return Brand::create([
            'name' => 'Other Feature Settings Community',
            'slug' => 'other-feature-settings-community',
            'domain' => 'other-feature-settings-community.test',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#DCECF7',
            'theme_bg' => '#F8FAFC',
            'registration_mode' => 'invite',
            'is_invite_only' => true,
        ]);
    }
}
