<?php

namespace Tests\Feature;

use App\Livewire\AdminOrders;
use App\Livewire\ChallengeDetail;
use App\Models\Course;
use App\Models\DigitalProduct;
use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\User;
use Database\Seeders\AutoCAD21DaysSeeder;
use Database\Seeders\Revit21DaysSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_gift_challenge_and_approved_student_bypasses_premium_lock(): void
    {
        app(Revit21DaysSeeder::class)->run();
        app(AutoCAD21DaysSeeder::class)->run();
        $admin = User::query()->where('is_admin', true)->firstOrFail();
        $learner = User::factory()->create(['account_type' => 'engineer']);
        $challenge = Expedition::query()->where('slug', '21-ngay-chinh-phuc-tool-autocad-bang-ai-agent')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(AdminOrders::class)
            ->set('grantUserId', $learner->id)
            ->set('grantType', 'challenge')
            ->set('grantResourceId', $challenge->id)
            ->call('grantAccess')
            ->assertHasNoErrors();

        $member = ExpeditionMember::query()
            ->where('expedition_id', $challenge->id)
            ->where('user_id', $learner->id)
            ->firstOrFail();

        $this->assertSame('approved', $member->status);
        $this->assertStringStartsWith('GIFT-ADMIN', $member->payment_ref);

        Livewire::actingAs($learner)
            ->test(ChallengeDetail::class, ['slug' => $challenge->slug])
            ->assertSet('premiumLocked', false);
    }

    public function test_admin_can_gift_course_and_product(): void
    {
        app(Revit21DaysSeeder::class)->run();
        $admin = User::query()->where('is_admin', true)->firstOrFail();
        $learner = User::factory()->create(['account_type' => 'engineer']);
        $course = Course::create([
            'title' => 'Test Course',
            'pillar' => 'delivery',
            'difficulty' => 'basic',
            'is_published' => true,
            'price' => 1000,
        ]);
        $product = DigitalProduct::create([
            'title' => 'Test Product',
            'price' => 1000,
            'delivery_type' => 'file',
            'is_published' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(AdminOrders::class)
            ->set('grantUserId', $learner->id)
            ->set('grantType', 'course')
            ->set('grantResourceId', $course->id)
            ->call('grantAccess')
            ->set('grantType', 'product')
            ->set('grantResourceId', $product->id)
            ->call('grantAccess')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('course_enrollments', [
            'user_id' => $learner->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('product_purchases', [
            'user_id' => $learner->id,
            'digital_product_id' => $product->id,
            'status' => 'active',
        ]);
    }
}
