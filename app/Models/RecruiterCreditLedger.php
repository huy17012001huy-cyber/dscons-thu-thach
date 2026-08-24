<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\HasBrand;

class RecruiterCreditLedger extends Model
{
    use HasBrand;
    protected $table = 'recruiter_credit_ledger';
    protected $fillable = ['brand_id', 'entitlement_id', 'recruiter_id', 'amount', 'type', 'reference'];
    public function entitlement(): BelongsTo { return $this->belongsTo(RecruiterEntitlement::class); }
    public function recruiter(): BelongsTo { return $this->belongsTo(User::class, 'recruiter_id'); }
}
