<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChallengeTaskCompletion extends Model
{
    protected $table = 'challenge_task_completions';

    protected $fillable = [
        'challenge_task_id', 'user_id', 'evidence', 'submission_payload',
        'status', 'reviewed_by', 'reviewed_at', 'review_note', 'is_late',
        'reject_count', 'score', 'auto_review_status', 'auto_review_payload',
        'rubric_payload',
    ];

    protected $casts = [
        'submission_payload' => 'array',
        'auto_review_payload' => 'array',
        'rubric_payload' => 'array',
        'reviewed_at' => 'datetime',
        'is_late' => 'boolean',
        'score' => 'integer',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(ChallengeTask::class, 'challenge_task_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ChallengeTaskReview::class, 'completion_id');
    }
}
