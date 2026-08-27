<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\EngineerCv;
use App\Models\EngineerProfile;
use App\Models\RecruiterProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTalentPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_admin_can_preview_cv_and_recruiter_experience(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $engineer = User::factory()->create(['account_type' => 'engineer']);
        $recruiter = User::factory()->create(['account_type' => 'recruiter']);
        $brand = Brand::create([
            'name' => 'Preview Community',
            'slug' => 'preview-community',
            'domain' => 'preview-community.test',
            'owner_id' => $admin->id,
            'status' => 'active',
            'has_cv' => true,
            'has_recruitment' => true,
        ]);
        EngineerProfile::withoutGlobalScopes()->create([
            'brand_id' => $brand->id,
            'user_id' => $engineer->id,
            'anonymized_code' => 'KYS-00001',
            'headline' => 'BIM Engineer',
            'is_searchable' => true,
        ]);
        $cv = EngineerCv::withoutGlobalScopes()->create([
            'brand_id' => $brand->id,
            'user_id' => $engineer->id,
            'status' => 'draft',
            'data' => ['skills' => [['name' => 'Revit']]],
        ]);
        $recruiterProfile = RecruiterProfile::withoutGlobalScopes()->create([
            'brand_id' => $brand->id,
            'user_id' => $recruiter->id,
            'company_name' => 'Test Talent',
            'company_slug' => 'test-talent',
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('community.manage.recruitment.preview.cv', ['community' => $brand->slug, 'cv' => $cv->id]))
            ->assertOk()
            ->assertSee('Chế độ xem trước của admin');

        $this->get(route('community.manage.recruitment.preview.recruiter', ['community' => $brand->slug, 'recruiter' => $recruiterProfile->id]))
            ->assertOk()
            ->assertSee('Chế độ xem trước của admin');
    }
}
