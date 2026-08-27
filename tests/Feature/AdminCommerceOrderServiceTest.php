<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\DigitalProduct;
use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Commerce\Application\AdminCommerceOrderService;
use Tests\TestCase;

final class AdminCommerceOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_admin_cannot_grant_a_product_from_another_community(): void
    {
        $admin = User::factory()->create();
        $admin->brandRoles()->attach(brand()->id, ['role' => 'admin']);
        $learner = User::factory()->create();
        $other = Brand::create([
            'name' => 'Other community',
            'slug' => 'other-community',
            'domain' => 'other-community.test',
            'status' => 'active',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#E1F4F7',
            'theme_bg' => '#F7FAFC',
        ]);
        $product = DigitalProduct::withoutGlobalScopes()->create([
            'brand_id' => $other->id,
            'title' => 'Other community product',
            'price' => 100000,
            'delivery_type' => 'file',
            'is_published' => true,
        ]);

        $this->expectException(ModelNotFoundException::class);

        app(AdminCommerceOrderService::class)->grant('product', $learner->id, $product->id, $admin);
    }

    public function test_community_admin_can_activate_a_pending_challenge_order(): void
    {
        $admin = User::factory()->create();
        $admin->brandRoles()->attach(brand()->id, ['role' => 'admin']);
        $learner = User::factory()->create();
        $challenge = Expedition::create([
            'title' => 'Activation challenge',
            'slug' => 'activation-challenge',
            'description' => 'Activation test.',
            'difficulty' => 'normal',
            'required_days' => 7,
            'max_members' => 50,
            'created_by' => $admin->id,
            'leader_id' => $admin->id,
            'status' => 'active',
        ]);
        $member = ExpeditionMember::create([
            'brand_id' => brand()->id,
            'expedition_id' => $challenge->id,
            'user_id' => $learner->id,
            'joined_at' => now(),
            'status' => 'pending_payment',
        ]);

        $result = app(AdminCommerceOrderService::class)->activate('challenge', $member->id, $admin);

        self::assertNotNull($result);
        self::assertSame($learner->id, $result->user->id);
        self::assertSame('Activation challenge', $result->label);
        $this->assertDatabaseHas('expedition_members', [
            'id' => $member->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $learner->id]);
    }
}
