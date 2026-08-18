<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonTask extends Model
{
    protected $fillable = ['lesson_id', 'title', 'description', 'type', 'order_index', 'is_required'];

    protected $casts = ['is_required' => 'boolean'];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(TaskSubmission::class, 'lesson_task_id');
    }
}
