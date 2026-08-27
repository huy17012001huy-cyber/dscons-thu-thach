<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\HasBrand;

/**
 * @property int $brand_id
 * @property int $recruiter_id
 * @property int $order_id
 * @property int $credits_total
 * @property int $credits_reserved
 * @property int $credits_used
 * @property \Illuminate\Support\Carbon|null $expires_at
 */
class RecruiterEntitlement extends Model
{
    use HasBrand;
    protected $fillable = ['brand_id', 'recruiter_id', 'order_id', 'credits_total', 'credits_reserved', 'credits_used', 'starts_at', 'expires_at'];
    protected $casts = ['starts_at' => 'datetime', 'expires_at' => 'datetime'];
    /** @return BelongsTo<User, $this> */
    public function recruiter(): BelongsTo { return $this->belongsTo(User::class, 'recruiter_id'); }

    /** @return BelongsTo<RecruiterOrder, $this> */
    public function order(): BelongsTo { return $this->belongsTo(RecruiterOrder::class, 'order_id'); }

    /** @return HasMany<RecruiterCreditLedger, $this> */
    public function ledger(): HasMany { return $this->hasMany(RecruiterCreditLedger::class, 'entitlement_id'); }
    public function availableCredits(): int { return max(0, $this->credits_total - $this->credits_reserved - $this->credits_used); }
    public function isUsable(): bool { return !$this->expires_at || $this->expires_at->isFuture(); }
}
