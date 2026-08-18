<?php

namespace App\Models\Concerns;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasBrand
{
    public static function bootHasBrand(): void
    {
        static::addGlobalScope('brand', function (Builder $query) {
            if (app()->bound('brand')) {
                $query->where($query->getModel()->getTable() . '.brand_id', brand()->id);
            }
        });

        static::creating(function ($model) {
            if (app()->bound('brand') && !$model->brand_id) {
                $model->brand_id = brand()->id;
            }
        });
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
}
