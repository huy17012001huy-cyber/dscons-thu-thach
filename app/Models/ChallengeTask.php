<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ChallengeTask extends Model
{
    protected $fillable = [
        'expedition_id', 'day_number', 'label', 'title', 'description',
        'sop_content', 'video_url', 'meeting_at', 'deadline_override_at',
        'evidence_type', 'evidence_label', 'admin_note', 'locked_until',
        'is_contest', 'contest_title', 'contest_description',
        'duration_hours', 'contest_duration_hours',
        'reward_file_path', 'reward_file_label', 'quiz_json',
    ];

    protected $casts = [
        'meeting_at' => 'datetime',
        'deadline_override_at' => 'datetime',
        'locked_until' => 'datetime',
        'is_contest' => 'boolean',
        'duration_hours' => 'integer',
        'contest_duration_hours' => 'integer',
        'quiz_json' => 'array',
    ];

    public function expedition(): BelongsTo
    {
        return $this->belongsTo(Expedition::class);
    }

    public function completedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'challenge_task_completions')
            ->withPivot('evidence')
            ->withTimestamps();
    }
}
