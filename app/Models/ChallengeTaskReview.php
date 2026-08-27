<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeTaskReview extends Model
{
    public $timestamps = false;

    protected $fillable = ['completion_id', 'reviewer_id', 'status', 'note', 'score', 'rubric_payload', 'created_at'];

    protected $casts = ['rubric_payload' => 'array', 'created_at' => 'datetime', 'score' => 'integer'];

    /** @return BelongsTo<ChallengeTaskCompletion, $this> */
    public function completion(): BelongsTo
    {
        return $this->belongsTo(ChallengeTaskCompletion::class, 'completion_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
