<?php

namespace Tests\Feature;

use App\Livewire\RecruiterDashboard;
use App\Livewire\EngineerCvPage;
use App\Livewire\EngineerRecruitmentRequestsPage;
use App\Livewire\AdminRecruitment;
use App\Models\Brand;
use App\Models\Conversation;
use App\Models\EngineerCv;
use App\Models\EngineerProfile;
use App\Models\RecruiterEntitlement;
use App\Models\RecruiterOrder;
use App\Models\RecruiterPlan;
use App\Models\RecruiterProfile;
use App\Models\RecruitmentContactRequest;
use App\Models\User;
use App\Services\RecruiterContactService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class RecruitmentMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_recruiter_dashboard_does_not_render_engineer_contact_data(): void
    {
        [$recruiter, $engineer] = $this->makeAccounts();
        $this->makeCv($engineer);

        Notification::fake();
        Livewire::actingAs($recruiter)
            ->test(RecruiterDashboard::class)
            ->set('jobDescription', 'Cần kỹ sư Revit BIM tối thiểu 3 năm')
            ->assertSee('KYS-')
            ->assertDontSee($engineer->email)
            ->assertDontSee('0900000000');
    }

    public function test_engineer_must_accept_before_recruiter_contact_is_revealed(): void
    {
        [$recruiter, $engineer] = $this->makeAccounts();
        $cv = $this->makeCv($engineer);
        $entitlement = $this->makeEntitlement($recruiter);

        Notification::fake();
        $request = app(RecruiterContactService::class)->request($recruiter, $engineer->engineerProfile, $cv);

        $this->assertSame('pending', $request->status);
        $this->assertSame(1, $entitlement->fresh()->credits_reserved);
        $this->assertDatabaseMissing('conversations', ['contact_request_id' => $request->id]);

        app(RecruiterContactService::class)->respond($request, $engineer, true);

        $this->assertDatabaseHas('recruitment_contact_requests', ['id' => $request->id, 'status' => 'accepted']);
        $this->assertDatabaseHas('conversations', ['contact_request_id' => $request->id, 'conversation_type' => 'recruitment']);
        $this->assertSame(1, $entitlement->fresh()->credits_used);
    }

    public function test_rejected_contact_request_refunds_reserved_credit(): void
    {
        [$recruiter, $engineer] = $this->makeAccounts();
        $cv = $this->makeCv($engineer);
        $entitlement = $this->makeEntitlement($recruiter);
        $request = app(RecruiterContactService::class)->request($recruiter, $engineer->engineerProfile, $cv);

        app(RecruiterContactService::class)->respond($request, $engineer, false);

        $fresh = $entitlement->fresh();
        $this->assertSame(0, $fresh->credits_reserved);
        $this->assertSame(0, $fresh->credits_used);
        $this->assertDatabaseHas('recruiter_credit_ledger', ['type' => 'refund', 'amount' => 1]);
    }

    public function test_expired_contact_request_refunds_reserved_credit_once(): void
    {
        [$recruiter, $engineer] = $this->makeAccounts();
        $cv = $this->makeCv($engineer);
        $entitlement = $this->makeEntitlement($recruiter);
        $request = app(RecruiterContactService::class)->request($recruiter, $engineer->engineerProfile, $cv);
        $request->forceFill(['created_at' => now()->subDays(8)])->saveQuietly();

        $this->artisan('recruitment:expire-requests')->assertSuccessful();

        $this->assertDatabaseHas('recruitment_contact_requests', ['id' => $request->id, 'status' => 'expired']);
        $this->assertSame(0, $entitlement->fresh()->credits_reserved);
        $this->assertSame(1, $entitlement->fresh()->ledger()->where('type', 'refund')->count());
    }

    public function test_recruitment_roles_cannot_cross_into_each_other(): void
    {
        [$recruiter, $engineer] = $this->makeAccounts();
        \App\Models\Membership::factory()->active()->create(['user_id' => $recruiter->id]);

        $this->actingAs($engineer)->get('/nha-tuyen-dung/dashboard')->assertForbidden();
        $this->actingAs($recruiter)->get('/ho-so-cv')->assertForbidden();
        $this->actingAs($recruiter)->get('/feed')->assertForbidden();
    }

    public function test_engineer_can_save_and_publish_a_structured_cv(): void
    {
        [, $engineer] = $this->makeAccounts();

        Livewire::actingAs($engineer)
            ->test(EngineerCvPage::class)
            ->set('headline', 'MEP Design Engineer')
            ->set('discipline', 'MEP')
            ->set('yearsExperience', 4)
            ->set('skillsText', 'Revit, HVAC, Navisworks')
            ->set('experiencesText', 'Thiết kế HVAC — Dự án văn phòng')
            ->call('save', true)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('engineer_cvs', ['user_id' => $engineer->id, 'status' => 'published', 'template' => 'technical-clean']);
        $this->assertDatabaseHas('engineer_profiles', ['user_id' => $engineer->id, 'is_searchable' => true]);
    }

    public function test_admin_is_not_treated_as_an_engineer_for_cv_access(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'account_type' => 'engineer']);

        $this->actingAs($admin)->get('/ho-so-cv')->assertForbidden();
        $this->actingAs($admin)->get('/c/dscons/ho-so-cv')->assertForbidden();
        $this->assertFalse($admin->fresh()->isEngineer());
    }

    public function test_recruitment_requests_have_a_separate_engineer_page(): void
    {
        [, $engineer] = $this->makeAccounts();

        Livewire::actingAs($engineer)
            ->test(EngineerRecruitmentRequestsPage::class)
            ->assertSee('Yêu cầu tuyển dụng')
            ->assertSee('Chỉnh CV của tôi')
            ->assertDontSee('Tiêu đề chuyên môn');
    }

    public function test_admin_can_view_all_cv_and_recruitment_requests_for_current_community(): void
    {
        [$recruiter, $engineer] = $this->makeAccounts();
        $cv = $this->makeCv($engineer);
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)
            ->test(AdminRecruitment::class)
            ->assertSee($cv->title)
            ->assertSee($engineer->email);

        $this->actingAs($admin)
            ->get('/c/dscons/manage/recruitment')
            ->assertOk()
            ->assertSee($cv->title);
    }

    private function makeAccounts(): array
    {
        $recruiter = User::factory()->create(['account_type' => 'recruiter']);
        RecruiterProfile::create(['user_id' => $recruiter->id, 'company_name' => 'BIM Test Co', 'company_slug' => 'bim-test-'.$recruiter->id, 'verification_status' => 'verified', 'verified_at' => now()]);
        $engineer = User::factory()->create(['email' => 'engineer@example.test', 'account_type' => 'engineer']);
        return [$recruiter, $engineer];
    }

    private function makeCv(User $engineer): EngineerCv
    {
        $profile = EngineerProfile::create(['brand_id' => brand()->id, 'user_id' => $engineer->id, 'anonymized_code' => 'KYS-'.$engineer->id, 'headline' => 'BIM Coordinator', 'discipline' => 'BIM', 'years_experience' => 5, 'contact_email' => $engineer->email, 'contact_phone' => '0900000000', 'is_searchable' => true]);
        return EngineerCv::create(['brand_id' => brand()->id, 'user_id' => $engineer->id, 'status' => 'published', 'data' => ['years_experience' => 5, 'skills' => [['name' => 'Revit'], ['name' => 'Navisworks']], 'experiences' => [['role' => 'BIM Coordinator']]]]);
    }

    private function makeEntitlement(User $recruiter): RecruiterEntitlement
    {
        $plan = RecruiterPlan::create(['name' => 'Test', 'contact_credits' => 3, 'duration_days' => 30, 'price' => 0, 'is_active' => true]);
        $order = RecruiterOrder::create(['recruiter_id' => $recruiter->id, 'plan_id' => $plan->id, 'status' => 'active', 'payment_ref' => 'TEST-'.$recruiter->id, 'amount' => 0, 'amount_paid' => 0, 'paid_at' => now()]);
        return RecruiterEntitlement::create(['recruiter_id' => $recruiter->id, 'order_id' => $order->id, 'credits_total' => 3, 'starts_at' => now(), 'expires_at' => now()->addDays(30)]);
    }
}
