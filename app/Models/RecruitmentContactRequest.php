<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Concerns\HasBrand;

class RecruitmentContactRequest extends Model
{
    use HasBrand;
    protected $fillable = ['brand_id', 'recruiter_id', 'engineer_id', 'cv_id', 'entitlement_id', 'status', 'message', 'reserved_at', 'responded_at', 'contact_revealed_at'];
    protected $casts = ['reserved_at' => 'datetime', 'responded_at' => 'datetime', 'contact_revealed_at' => 'datetime'];
    public function recruiter(): BelongsTo { return $this->belongsTo(User::class, 'recruiter_id'); }
    public function engineer(): BelongsTo { return $this->belongsTo(User::class, 'engineer_id'); }
    public function cv(): BelongsTo { return $this->belongsTo(EngineerCv::class, 'cv_id'); }
    public function entitlement(): BelongsTo { return $this->belongsTo(RecruiterEntitlement::class, 'entitlement_id'); }
    public function conversation(): HasOne { return $this->hasOne(Conversation::class, 'contact_request_id'); }
    public function isAccepted(): bool { return $this->status === 'accepted'; }
}
