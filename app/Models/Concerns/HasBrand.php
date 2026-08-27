<?php

namespace App\Models\Concerns;

use App\Core\CommunityContext;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasBrand
{
    public static function bootHasBrand(): void
    {
        static::addGlobalScope('brand', function (Builder $query) {
            $brand = app(CommunityContext::class)->current();

            if ($brand) {
                $query->where($query->getModel()->getTable().'.brand_id', $brand->id);
            }
        });

        static::creating(function ($model) {
            $brand = app(CommunityContext::class)->current();

            if ($brand && ! $model->brand_id) {
                $model->brand_id = $brand->id;
            }
        });
    }

    /** @return BelongsTo<Brand, $this> */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
}
