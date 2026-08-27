<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\RecruiterProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Recruitment\Application\RecruitmentAdminManagementService;
use Tests\TestCase;

final class RecruitmentAdminManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_admin_can_verify_recruiters_and_manage_plans_in_its_community(): void
    {
        $admin = $this->communityAdmin();
        $recruiter = User::factory()->create(['account_type' => 'recruiter']);
        $profile = RecruiterProfile::create([
            'brand_id' => brand()->id,
            'user_id' => $recruiter->id,
            'company_name' => 'DSCons Talent',
            'company_slug' => 'dscons-talent',
            'verification_status' => 'pending',
        ]);
        $service = app(RecruitmentAdminManagementService::class);

        $verified = $service->approveRecruiter($profile->id, $admin, false);
        $plan = $service->createPlan($admin, false, [
            'name' => 'Starter',
            'description' => null,
            'contact_credits' => 5,
            'duration_days' => 30,
            'price' => 500000,
        ]);

        self::assertSame('verified', $verified->verification_status);
        self::assertSame($admin->id, $verified->reviewed_by);
        self::assertSame(brand()->id, $plan->brand_id);
        self::assertTrue($plan->is_active);
        self::assertFalse($service->togglePlan($plan->id, $admin, false)->is_active);
    }

    public function test_community_admin_cannot_change_recruitment_data_from_another_community_but_super_admin_can(): void
    {
        $admin = $this->communityAdmin();
        $other = $this->otherBrand();
        $recruiter = User::factory()->create(['account_type' => 'recruiter']);
        $profile = RecruiterProfile::withoutGlobalScopes()->create([
            'brand_id' => $other->id,
            'user_id' => $recruiter->id,
            'company_name' => 'Other Talent',
            'company_slug' => 'other-talent',
            'verification_status' => 'pending',
        ]);

        $this->expectException(ModelNotFoundException::class);
        app(RecruitmentAdminManagementService::class)->approveRecruiter($profile->id, $admin, false);
    }

    public function test_super_admin_can_manage_a_recruiter_from_another_community_in_global_mode(): void
    {
        $superAdmin = User::factory()->create(['is_admin' => true]);
        $other = $this->otherBrand();
        $recruiter = User::factory()->create(['account_type' => 'recruiter']);
        $profile = RecruiterProfile::withoutGlobalScopes()->create([
            'brand_id' => $other->id,
            'user_id' => $recruiter->id,
            'company_name' => 'Other Talent',
            'company_slug' => 'other-talent-global',
            'verification_status' => 'pending',
        ]);

        $updated = app(RecruitmentAdminManagementService::class)->rejectRecruiter($profile->id, $superAdmin, true);

        self::assertSame('rejected', $updated->verification_status);
        self::assertSame($superAdmin->id, $updated->reviewed_by);
    }

    private function communityAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->brandRoles()->attach(brand()->id, ['role' => 'admin']);

        return $admin;
    }

    private function otherBrand(): Brand
    {
        return Brand::create([
            'name' => 'Other Recruitment Community',
            'slug' => 'other-recruitment-community',
            'domain' => 'other-recruitment-community.test',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#DCECF7',
            'theme_bg' => '#F8FAFC',
            'registration_mode' => 'invite',
            'is_invite_only' => true,
        ]);
    }
}
