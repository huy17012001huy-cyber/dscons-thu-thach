<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\DigitalProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Commerce\Application\ProductPurchaseOutcome;
use Modules\Commerce\Application\ProductPurchaseService;
use Tests\TestCase;

final class ProductPurchaseServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_and_paid_products_create_the_correct_purchase_state_once(): void
    {
        $user = User::factory()->create();
        $free = $this->product('Free resource', 0);
        $paid = $this->product('Paid resource', 300000);
        $service = app(ProductPurchaseService::class);

        self::assertSame(ProductPurchaseOutcome::Activated, $service->purchase($free->id, $user));
        self::assertSame(ProductPurchaseOutcome::AlreadyOwned, $service->purchase($free->id, $user));
        self::assertSame(ProductPurchaseOutcome::PendingPayment, $service->purchase($paid->id, $user));
        self::assertSame(ProductPurchaseOutcome::AlreadyPending, $service->purchase($paid->id, $user));
        $this->assertDatabaseHas('product_purchases', [
            'brand_id' => brand()->id,
            'user_id' => $user->id,
            'digital_product_id' => $free->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('product_purchases', [
            'brand_id' => brand()->id,
            'user_id' => $user->id,
            'digital_product_id' => $paid->id,
            'status' => 'pending_payment',
        ]);
    }

    public function test_purchase_cannot_target_a_product_from_another_community(): void
    {
        $otherBrand = $this->otherBrand();
        $product = DigitalProduct::withoutGlobalScopes()->create([
            'brand_id' => $otherBrand->id,
            'title' => 'Other resource',
            'price' => 0,
            'is_published' => true,
        ]);

        $this->expectException(ModelNotFoundException::class);
        app(ProductPurchaseService::class)->purchase($product->id, User::factory()->create());
    }

    private function product(string $title, int $price): DigitalProduct
    {
        return DigitalProduct::create([
            'brand_id' => brand()->id,
            'title' => $title,
            'price' => $price,
            'is_published' => true,
        ]);
    }

    private function otherBrand(): Brand
    {
        return Brand::create([
            'name' => 'Other Product Community',
            'slug' => 'other-product-community',
            'domain' => 'other-product-community.test',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#DCECF7',
            'theme_bg' => '#F8FAFC',
            'registration_mode' => 'invite',
            'is_invite_only' => true,
        ]);
    }
}
