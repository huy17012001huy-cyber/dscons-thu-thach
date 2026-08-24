<?php

namespace Tests\Feature;

use App\Livewire\ChallengePage;
use App\Livewire\AcademyDetail;
use App\Livewire\MarketplacePage;
use App\Livewire\OrdersPage;
use App\Models\ChallengeTask;
use App\Models\Course;
use App\Models\DigitalProduct;
use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class LearningCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_challenge_tabs_show_approved_progress_without_boss_copy(): void
    {
        $user = User::factory()->create();
        $challenge = Expedition::create([
            'title' => 'BIM Sprint', 'slug' => 'bim-sprint', 'description' => 'Lộ trình BIM thực chiến',
            'boss_name' => 'Mục tiêu nội bộ', 'difficulty' => 'normal', 'required_days' => 7,
            'max_members' => 50, 'created_by' => $user->id, 'leader_id' => $user->id, 'status' => 'active',
        ]);
        $task = ChallengeTask::create(['expedition_id' => $challenge->id, 'day_number' => 1, 'title' => 'Bài 1']);
        ExpeditionMember::create([
            'expedition_id' => $challenge->id, 'user_id' => $user->id, 'joined_at' => now(),
            'status' => 'approved', 'personal_starts_at' => now(),
        ]);
        DB::table('challenge_task_completions')->insert([
            'challenge_task_id' => $task->id, 'user_id' => $user->id, 'evidence' => 'done', 'status' => 'approved',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Livewire::actingAs($user)->test(ChallengePage::class)
            ->assertSet('tab', 'active')
            ->assertSee('BIM Sprint')
            ->assertSee('1/1 bài đã duyệt')
            ->assertDontSee('Mục tiêu nội bộ');
    }

    public function test_marketplace_contains_featured_challenge_course_and_resource(): void
    {
        $user = User::factory()->create();
        Expedition::create([
            'title' => 'Featured Challenge', 'slug' => 'featured-challenge', 'boss_name' => 'Internal',
            'difficulty' => 'hard', 'required_days' => 14, 'max_members' => 50, 'created_by' => $user->id,
            'leader_id' => $user->id, 'status' => 'open', 'is_featured' => true,
        ]);
        Course::create(['title' => 'Featured Course', 'pillar' => 'delivery', 'difficulty' => 'basic', 'is_published' => true, 'is_featured' => true]);
        DigitalProduct::create(['title' => 'Featured Resource', 'price' => 0, 'is_published' => true, 'is_featured' => true]);

        Livewire::actingAs($user)->test(MarketplacePage::class)
            ->assertSee('Featured Challenge')
            ->assertSee('Featured Course')
            ->assertSee('Featured Resource')
            ->assertSee('Thử thách hot')
            ->assertSee('Khóa học')
            ->assertSee('Nổi bật & trending tuần này')
            ->assertSee('Độ khó: Khó')
            ->assertDontSee('Tất cả trạng thái');
    }

    public function test_orders_page_aggregates_membership_and_unowned_content(): void
    {
        $user = User::factory()->create();
        Membership::create(['user_id' => $user->id, 'tier' => 'free', 'status' => 'active', 'starts_at' => now()]);
        Course::create(['title' => 'Chưa mua', 'pillar' => 'offer', 'difficulty' => 'basic', 'is_published' => true]);

        Livewire::actingAs($user)->test(OrdersPage::class)
            ->assertSee('Membership hiện tại')
            ->assertSee('Chưa mua');
    }

    public function test_expired_member_can_open_orders_page_to_renew(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        Membership::create(['user_id' => $user->id, 'tier' => 'premium', 'status' => 'expired', 'expires_at' => now()->subDay()]);

        $this->actingAs($user)->get('/goi-don-hang')->assertOk();
    }

    public function test_non_member_can_purchase_a_course_individually(): void
    {
        $user = User::factory()->create(['class' => 'delivery_assassin']);
        $course = Course::create([
            'title' => 'Mua lẻ BIM',
            'pillar' => 'delivery',
            'difficulty' => 'basic',
            'price' => 500000,
            'is_published' => true,
        ]);

        Livewire::actingAs($user)
            ->test(AcademyDetail::class, ['id' => $course->id])
            ->assertSet('premiumLocked', false)
            ->call('enroll')
            ->assertSet('pendingPayment', true);

        $this->assertDatabaseHas('course_enrollments', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'pending_payment',
        ]);
    }
}
