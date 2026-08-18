<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $fillable = [
        'module_id', 'title', 'description', 'lesson_type', 'video_url', 'thumbnail', 'content',
        'xp_reward', 'aip_reward', 'duration_minutes', 'order_index',
        'is_locked_by_default',
    ];

    protected $casts = ['is_locked_by_default' => 'boolean'];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(LessonTask::class)->orderBy('order_index');
    }

    public function prerequisites(): HasMany
    {
        return $this->hasMany(LessonPrerequisite::class);
    }

    public function isUnlockedFor(User $user): bool
    {
        if (!$this->is_locked_by_default) {
            return true;
        }

        // Check explicit prerequisites first
        $requiredIds = $this->prerequisites()->pluck('required_lesson_id');

        if ($requiredIds->isEmpty()) {
            // No explicit prerequisites — use implicit: previous lesson in same module
            if ($this->order_index === 0) {
                return true; // First lesson always unlocked
            }

            $prev = self::where('module_id', $this->module_id)
                ->where('order_index', '<', $this->order_index)
                ->orderByDesc('order_index')
                ->first();

            if (!$prev) {
                return true;
            }

            $requiredIds = collect([$prev->id]);
        }

        $completedCount = LessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $requiredIds)
            ->whereNotNull('completed_at')
            ->count();

        return $completedCount >= $requiredIds->count();
    }

    public function isCompletedBy(User $user): bool
    {
        return LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $this->id)
            ->whereNotNull('completed_at')
            ->exists();
    }
}
