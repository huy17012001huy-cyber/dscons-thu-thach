<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonPrerequisite extends Model
{
    public $timestamps = false;

    protected $fillable = ['lesson_id', 'required_lesson_id'];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function requiredLesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'required_lesson_id');
    }
}
