<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\HasBrand;

/**
 * @property int $brand_id
 * @property int $user_id
 * @property string|null $verification_status
 */
class RecruiterProfile extends Model
{
    use HasBrand;

    protected $fillable = ['brand_id', 'user_id', 'company_name', 'company_slug', 'business_email', 'website', 'industry', 'description', 'verification_status', 'reviewed_by', 'verified_at', 'review_note'];

    protected $casts = ['verified_at' => 'datetime'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function isVerified(): bool { return $this->verification_status === 'verified'; }
}
