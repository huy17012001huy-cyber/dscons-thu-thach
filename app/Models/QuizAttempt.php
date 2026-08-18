<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'challenge_task_id', 'question_index',
        'selected_letter', 'is_correct', 'xp_awarded', 'answered_at',
    ];

    protected $casts = [
        'is_correct'  => 'boolean',
        'xp_awarded'  => 'boolean',
        'answered_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ChallengeTask::class, 'challenge_task_id');
    }
}
