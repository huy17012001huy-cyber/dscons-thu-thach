<?php

declare(strict_types=1);

namespace Modules\Commerce\Application;

use App\Core\CommunityContext;
use App\Models\DigitalProduct;
use App\Models\ProductPurchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ProductPurchaseService
{
    public function __construct(private readonly CommunityContext $context) {}

    public function purchase(int $productId, User $user): ProductPurchaseOutcome
    {
        return DB::transaction(function () use ($productId, $user): ProductPurchaseOutcome {
            $brand = $this->context->require();
            $product = DigitalProduct::query()
                ->where('brand_id', $brand->id)
                ->where('is_published', true)
                ->whereKey($productId)
                ->lockForUpdate()
                ->firstOrFail();
            $existing = ProductPurchase::query()
                ->where('brand_id', $brand->id)
                ->where('user_id', $user->id)
                ->where('digital_product_id', $product->id)
                ->lockForUpdate()
                ->first();
            if ($existing?->status === 'active') {
                return ProductPurchaseOutcome::AlreadyOwned;
            }
            if ($existing) {
                return ProductPurchaseOutcome::AlreadyPending;
            }

            $isFree = $product->isFree();
            ProductPurchase::create([
                'brand_id' => $brand->id,
                'user_id' => $user->id,
                'digital_product_id' => $product->id,
                'status' => $isFree ? 'active' : 'pending_payment',
                'paid_at' => $isFree ? now() : null,
            ]);

            return $isFree ? ProductPurchaseOutcome::Activated : ProductPurchaseOutcome::PendingPayment;
        });
    }
}
