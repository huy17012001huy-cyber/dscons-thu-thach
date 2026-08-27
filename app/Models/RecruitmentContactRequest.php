<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Concerns\HasBrand;

/**
 * @property int $brand_id
 * @property int $recruiter_id
 * @property int $engineer_id
 * @property int $cv_id
 * @property int $entitlement_id
 * @property string $status
 */
class RecruitmentContactRequest extends Model
{
    use HasBrand;
    protected $fillable = ['brand_id', 'recruiter_id', 'engineer_id', 'cv_id', 'entitlement_id', 'status', 'message', 'reserved_at', 'responded_at', 'contact_revealed_at'];
    protected $casts = ['reserved_at' => 'datetime', 'responded_at' => 'datetime', 'contact_revealed_at' => 'datetime'];
    /** @return BelongsTo<User, $this> */
    public function recruiter(): BelongsTo { return $this->belongsTo(User::class, 'recruiter_id'); }

    /** @return BelongsTo<User, $this> */
    public function engineer(): BelongsTo { return $this->belongsTo(User::class, 'engineer_id'); }

    /** @return BelongsTo<EngineerCv, $this> */
    public function cv(): BelongsTo { return $this->belongsTo(EngineerCv::class, 'cv_id'); }

    /** @return BelongsTo<RecruiterEntitlement, $this> */
    public function entitlement(): BelongsTo { return $this->belongsTo(RecruiterEntitlement::class, 'entitlement_id'); }

    /** @return HasOne<Conversation, $this> */
    public function conversation(): HasOne { return $this->hasOne(Conversation::class, 'contact_request_id'); }
    public function isAccepted(): bool { return $this->status === 'accepted'; }
}
