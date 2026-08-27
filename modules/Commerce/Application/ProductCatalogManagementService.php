<?php

declare(strict_types=1);

namespace Modules\Commerce\Application;

use App\Core\CommunityContext;
use App\Models\DigitalProduct;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ProductCatalogManagementService
{
    public function __construct(private readonly CommunityContext $context) {}

    /** @param array<string, mixed> $attributes */
    public function save(?int $productId, User $actor, array $attributes): ?DigitalProduct
    {
        return DB::transaction(function () use ($productId, $actor, $attributes): ?DigitalProduct {
            $brand = $this->context->require();
            if (! $actor->isCommunityAdmin($brand->id)) {
                return null;
            }

            $product = $productId === null ? null : DigitalProduct::query()
                ->where('brand_id', $brand->id)
                ->whereKey($productId)
                ->lockForUpdate()
                ->firstOrFail();
            if ($product) {
                $product->update($attributes);

                return $product->refresh();
            }

            return DigitalProduct::create([...$attributes, 'brand_id' => $brand->id]);
        });
    }

    public function togglePublished(int $productId, User $actor): ?DigitalProduct
    {
        return DB::transaction(function () use ($productId, $actor): ?DigitalProduct {
            $product = $this->managedProduct($productId, $actor);
            if (! $product) {
                return null;
            }

            $product->update(['is_published' => ! $product->is_published]);

            return $product->refresh();
        });
    }

    public function delete(int $productId, User $actor): ?DigitalProduct
    {
        return DB::transaction(function () use ($productId, $actor): ?DigitalProduct {
            $product = $this->managedProduct($productId, $actor);
            if (! $product) {
                return null;
            }

            $product->delete();

            return $product;
        });
    }

    private function managedProduct(int $productId, User $actor): ?DigitalProduct
    {
        $brand = $this->context->require();
        if (! $actor->isCommunityAdmin($brand->id)) {
            return null;
        }

        return DigitalProduct::query()
            ->where('brand_id', $brand->id)
            ->whereKey($productId)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
