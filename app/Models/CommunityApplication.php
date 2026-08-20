<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityApplication extends Model
{
    protected $fillable = [
        'applicant_id', 'name', 'slug', 'tagline', 'description', 'logo_path', 'banner_path',
        'teaching_topic', 'program_description', 'proposed_premium_price',
        'proposed_sepay_account', 'proposed_sepay_bank', 'status', 'reviewed_by', 'review_note',
    ];

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
