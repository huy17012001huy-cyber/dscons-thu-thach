<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\HasBrand;

class LessonProgress extends Model
{
    use HasBrand;
    public $timestamps = false;

    protected $table = 'lesson_progress';

    protected $fillable = ['user_id', 'lesson_id', 'completed_at', 'brand_id'];

    protected $casts = ['completed_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
