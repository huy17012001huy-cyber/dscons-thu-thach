<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** @property int $brand_id @property int $recruiter_id @property int $plan_id @property int $amount @property RecruiterPlan $plan */
class RecruiterOrder extends Model
{
    use HasBrand;

    protected $fillable = ['brand_id', 'recruiter_id', 'plan_id', 'status', 'payment_ref', 'amount', 'amount_paid', 'paid_at'];

    protected $casts = ['paid_at' => 'datetime'];

    /** @return BelongsTo<User, $this> */
    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recruiter_id');
    }

    /** @return BelongsTo<RecruiterPlan, $this> */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(RecruiterPlan::class, 'plan_id');
    }

    /** @return HasOne<RecruiterEntitlement, $this> */
    public function entitlement(): HasOne
    {
        return $this->hasOne(RecruiterEntitlement::class, 'order_id');
    }
}
