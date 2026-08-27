<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property int|float $price
 * @property bool $is_featured
 * @property string|null $tool_key
 * @property string|null $tool_manifest_version
 * @property array<int, string>|null $supported_revit_versions
 * @property string|null $package_path
 * @property-read Brand|null $brand
 * @property-read int $active_purchases_count
 */
class DigitalProduct extends Model
{
    use HasBrand;

    protected $fillable = [
        'title', 'description', 'thumbnail', 'pillar', 'price',
        'delivery_type', 'file_path', 'file_name', 'access_url',
        'is_published', 'sort_order', 'brand_id', 'is_featured',
        'product_kind', 'tool_key', 'supported_revit_versions', 'tool_manifest_version', 'package_path', 'is_license_required',
    ];

    protected $casts = ['is_published' => 'boolean', 'is_featured' => 'boolean', 'is_license_required' => 'boolean', 'supported_revit_versions' => 'array'];

    /** @return HasMany<ProductPurchase, $this> */
    public function purchases(): HasMany
    {
        return $this->hasMany(ProductPurchase::class);
    }

    public function isFree(): bool
    {
        return $this->price <= 0;
    }
}
