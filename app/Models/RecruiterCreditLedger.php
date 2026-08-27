<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\HasBrand;

/** @property int $brand_id @property int $entitlement_id @property int $recruiter_id */
class RecruiterCreditLedger extends Model
{
    use HasBrand;
    protected $table = 'recruiter_credit_ledger';
    protected $fillable = ['brand_id', 'entitlement_id', 'recruiter_id', 'amount', 'type', 'reference'];
    /** @return BelongsTo<RecruiterEntitlement, $this> */
    public function entitlement(): BelongsTo { return $this->belongsTo(RecruiterEntitlement::class); }

    /** @return BelongsTo<User, $this> */
    public function recruiter(): BelongsTo { return $this->belongsTo(User::class, 'recruiter_id'); }
}
