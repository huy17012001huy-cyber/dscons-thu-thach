<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ChallengeSubmissionXpAward extends Model
{
    protected $fillable = ['challenge_task_completion_id', 'user_id'];

    /** @return BelongsTo<ChallengeTaskCompletion, $this> */
    public function completion(): BelongsTo
    {
        return $this->belongsTo(ChallengeTaskCompletion::class, 'challenge_task_completion_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
