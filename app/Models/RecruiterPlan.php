<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $brand_id
 * @property int $contact_credits
 * @property int|null $duration_days
 * @property-read Brand|null $brand
 */
class RecruiterPlan extends Model
{
    use HasBrand;

    protected $fillable = ['brand_id', 'name', 'description', 'contact_credits', 'duration_days', 'price', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /** @return HasMany<RecruiterOrder, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(RecruiterOrder::class, 'plan_id');
    }
}
