<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\HasBrand;

class RecruiterProfile extends Model
{
    use HasBrand;

    protected $fillable = ['brand_id', 'user_id', 'company_name', 'company_slug', 'business_email', 'website', 'industry', 'description', 'verification_status', 'reviewed_by', 'verified_at', 'review_note'];

    protected $casts = ['verified_at' => 'datetime'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function isVerified(): bool { return $this->verification_status === 'verified'; }
}
