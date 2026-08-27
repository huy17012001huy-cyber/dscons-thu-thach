<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property int $id @property int $brand_id @property int $price @property int|null $duration_days @property Brand|null $brand */
class MembershipPlan extends Model
{
    use HasBrand;

    protected $fillable = [
        'brand_id', 'tier', 'name', 'duration_days', 'price', 'benefits', 'status',
        'sepay_account', 'sepay_bank',
    ];

    protected $casts = [
        'benefits' => 'array',
        'price' => 'integer',
        'duration_days' => 'integer',
    ];

    /** @return BelongsTo<Brand, $this> */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isPremium(): bool
    {
        return $this->tier === 'premium';
    }
}
