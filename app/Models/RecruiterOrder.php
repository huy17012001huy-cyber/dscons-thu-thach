<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Concerns\HasBrand;

class RecruiterOrder extends Model
{
    use HasBrand;
    protected $fillable = ['brand_id', 'recruiter_id', 'plan_id', 'status', 'payment_ref', 'amount', 'amount_paid', 'paid_at'];
    protected $casts = ['paid_at' => 'datetime'];
    public function recruiter(): BelongsTo { return $this->belongsTo(User::class, 'recruiter_id'); }
    public function plan(): BelongsTo { return $this->belongsTo(RecruiterPlan::class, 'plan_id'); }
    public function entitlement(): HasOne { return $this->hasOne(RecruiterEntitlement::class, 'order_id'); }
}
