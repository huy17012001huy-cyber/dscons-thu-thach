<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property User $user
 * @property Expedition|null $expedition
 * @property \Carbon\CarbonInterface|null $created_at
 * @property \Carbon\CarbonInterface|null $joined_at
 */
class ExpeditionMember extends Model
{
    use HasBrand;

    public $timestamps = false;

    protected $fillable = [
        'expedition_id', 'user_id', 'class_at_join', 'joined_at', 'completed_at',
        'kicked_at', 'last_checkin_at', 'consecutive_missed_days', 'revenue_share_pct',
        'status', 'approved_at', 'approved_by', 'payment_amount', 'payment_ref', 'personal_starts_at',
        'miss_warned', 'video_feedback_url', 'video_feedback_status', 'video_feedback_at', 'video_feedback_note', 'brand_id',
        'deadline_overrides', 'completion_notified_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime', 'completed_at' => 'datetime', 'kicked_at' => 'datetime',
        'last_checkin_at' => 'datetime', 'approved_at' => 'datetime', 'personal_starts_at' => 'datetime',
        'completion_notified_at' => 'datetime',
        'payment_amount' => 'decimal:2',
        'deadline_overrides' => 'array',
    ];

    /** @return BelongsTo<Expedition, $this> */
    public function expedition(): BelongsTo
    {
        return $this->belongsTo(Expedition::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
