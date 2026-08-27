<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $expedition_id
 * @property int $day_number
 * @property string|null $title
 * @property string|null $sop_content
 * @property int|null $duration_hours
 * @property int|null $contest_duration_hours
 * @property bool $is_contest
 * @property \Illuminate\Support\Carbon|null $deadline_override_at
 */
class ChallengeTask extends Model
{
    protected $fillable = [
        'expedition_id', 'day_number', 'label', 'title', 'description',
        'sop_content', 'video_url', 'meeting_at', 'deadline_override_at',
        'evidence_type', 'evidence_label', 'admin_note', 'locked_until',
        'is_contest', 'contest_title', 'contest_description',
        'duration_hours', 'contest_duration_hours',
        'reward_file_path', 'reward_file_label', 'quiz_json', 'instruction_payload',
    ];

    protected $casts = [
        'meeting_at' => 'datetime',
        'deadline_override_at' => 'datetime',
        'locked_until' => 'datetime',
        'is_contest' => 'boolean',
        'duration_hours' => 'integer',
        'contest_duration_hours' => 'integer',
        'quiz_json' => 'array',
        'instruction_payload' => 'array',
    ];

    /** @return BelongsTo<Expedition, $this> */
    public function expedition(): BelongsTo
    {
        return $this->belongsTo(Expedition::class);
    }

    /** @return BelongsToMany<User, $this, \Illuminate\Database\Eloquent\Relations\Pivot> */
    public function completedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'challenge_task_completions')
            ->withPivot('evidence')
            ->withTimestamps();
    }
}
