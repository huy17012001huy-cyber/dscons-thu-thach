<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskSubmission extends Model
{
    use HasBrand;

    protected $fillable = [
        'lesson_task_id', 'user_id', 'content', 'file_url',
        'status', 'reviewed_by', 'reviewed_at', 'submitted_at', 'brand_id',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    /** @return BelongsTo<LessonTask, $this> */
    public function task(): BelongsTo
    {
        return $this->belongsTo(LessonTask::class, 'lesson_task_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
