<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\HasBrand;

class DigitalProduct extends Model
{
    use HasBrand;
    protected $fillable = [
        'title', 'description', 'thumbnail', 'pillar', 'price',
        'delivery_type', 'file_path', 'file_name', 'access_url',
        'is_published', 'sort_order', 'brand_id', 'is_featured',
    ];

    protected $casts = ['is_published' => 'boolean', 'is_featured' => 'boolean'];

    public function purchases(): HasMany
    {
        return $this->hasMany(ProductPurchase::class);
    }

    public function isFree(): bool
    {
        return $this->price <= 0;
    }
}
