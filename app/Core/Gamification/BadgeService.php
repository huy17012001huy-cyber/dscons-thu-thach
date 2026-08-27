<?php

declare(strict_types=1);

namespace App\Core\Gamification;

use App\Models\Badge;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Models\UserBadge;

final class BadgeService
{
    public function check(User $user): void
    {
        Badge::all()->each(function (Badge $badge) use ($user): void {
            if ($this->evaluate($user, $badge) && ! $this->hasEarned($user, $badge)) {
                $this->award($user, $badge);
            }
        });
    }

    public function award(User $user, Badge $badge): void
    {
        if ($this->hasEarned($user, $badge)) {
            return;
        }

        UserBadge::create([
            'user_id' => $user->id,
            'badge_id' => $badge->id,
            'earned_at' => now(),
        ]);
    }

    private function hasEarned(User $user, Badge $badge): bool
    {
        return UserBadge::query()
            ->where('user_id', $user->id)
            ->where('badge_id', $badge->id)
            ->exists();
    }

    private function evaluate(User $user, Badge $badge): bool
    {
        if (! $badge->condition_type || ! $badge->condition_value) {
            return false;
        }

        return match ($badge->condition_type) {
            'level_gte' => $user->level >= (int) $badge->condition_value,
            'post_count_gte' => $user->posts()->count() >= (int) $badge->condition_value,
            'comment_count_gte' => $user->comments()->count() >= (int) $badge->condition_value,
            'streak_gte' => $user->streak >= (int) $badge->condition_value,
            'bookmark_count_gte' => $user->bookmarks()->count() >= (int) $badge->condition_value,
            'answer_count_gte' => $user->questions()->count() >= (int) $badge->condition_value,
            'da_count_gte' => $user->da_count >= (int) $badge->condition_value,
            'expedition_created' => $user->expeditionMembers()
                ->whereHas('expedition', fn ($query) => $query->where('created_by', $user->id))
                ->exists(),
            'course_completed' => ! $user->affiliateEarnings()->exists()
                && CourseEnrollment::query()->where('user_id', $user->id)->whereNotNull('completed_at')->exists(),
            default => false,
        };
    }
}
