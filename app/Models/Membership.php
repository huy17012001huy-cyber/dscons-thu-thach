<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Carbon\CarbonInterface;
use Database\Factories\MembershipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property CarbonInterface|null $expires_at
 * @property-read Brand|null $brand
 */
class Membership extends Model
{
    /** @use HasFactory<MembershipFactory> */
    use HasBrand, HasFactory;

    protected $fillable = ['user_id', 'plan', 'tier', 'status', 'trial_ends_at', 'starts_at', 'expires_at', 'paid_amount', 'payment_ref', 'referred_by', 'brand_id'];

    protected $casts = ['trial_ends_at' => 'datetime', 'starts_at' => 'datetime', 'expires_at' => 'datetime'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'active'
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial'
            && ($this->trial_ends_at === null || $this->trial_ends_at->isFuture());
    }

    public function isPremium(): bool
    {
        return $this->tier === 'premium';
    }
}
