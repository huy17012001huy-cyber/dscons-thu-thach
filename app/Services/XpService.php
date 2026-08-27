<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\CommunityUserStat;
use App\Models\Post;
use App\Models\User;
use App\Models\XpTransaction;
use App\Notifications\GenericNotification;
use Illuminate\Database\Eloquent\Model;

class XpService
{
    /**
     * Engagement-based EXP.
     * You don't earn EXP by posting — the community gives it to you.
     * Post = 0. Like/comment on YOUR content = YOU get EXP.
     */
    const REWARDS = [
        // Engagement (content creator receives)
        'post_liked' => 2,
        'post_commented' => 3,
        'post_bookmarked' => 1,
        'comment_liked' => 1,
        'best_answer' => 25,
        'cot' => 50,

        // Base actions (minimal or zero)
        'post' => 0,   // No EXP for posting
        'comment' => 1,
        'login' => 1,

        // Expedition & Academy
        'expedition_checkin' => 5,
        'expedition_complete' => 100,
        'expedition_captain' => 200,
        'lesson_complete' => 10,
        'course_complete' => 50,
        'challenge' => 25,
        'affiliate' => 100,
        'quiz_correct' => 2,   // Per quiz question answered correctly (first time only)
    ];

    public function award(User $user, string $type, float $multiplier = 1.0, ?string $description = null, ?Model $reference = null): int
    {
        $base = self::REWARDS[$type] ?? 0;
        if ($base === 0) {
            return 0;
        }

        $stats = $this->communityStats($user);
        $streak = $stats instanceof CommunityUserStat ? $stats->streak : $user->streak;

        $streakMultiplier = match (true) {
            $streak >= 90 => 1.5,
            $streak >= 30 => 1.2,
            $streak >= 7 => 1.1,
            default => 1.0,
        };

        $total = (int) round($base * $multiplier * $streakMultiplier);

        XpTransaction::create([
            'user_id' => $user->id,
            'amount' => $total,
            'type' => $type,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->getKey(),
            'multiplier' => $multiplier * $streakMultiplier,
            'description' => $description,
        ]);

        if ($stats) {
            $stats->increment('xp', $total);
            $stats->refresh();
            $user->setAttribute('xp', (int) $stats->xp);
        } else {
            $user->increment('xp', $total);
        }
        $this->checkLevelUp($user);
        // Keep legacy user columns in sync for existing reports and integrations;
        // community_stats remains the source of truth when a community is active.
        $user->forceFill([
            'xp' => (int) $user->getAttribute('xp'),
            'level' => (int) $user->getAttribute('level'),
        ])->saveQuietly();

        // Power symbol fragments
        if (in_array($type, ['cot', 'post_liked', 'post_commented'], true) && $reference) {
            $pillar = $this->referencePillar($reference);
            if ($pillar) {
                $fragments = match ($type) {
                    'cot' => 3, default => 1
                };
                app(PowerSymbolService::class)->addFragments($user, $pillar, $fragments);
            }
        }

        app(BadgeService::class)->check($user);

        return $total;
    }

    public function checkLevelUp(User $user): bool
    {
        $leveled = false;

        $stats = $this->communityStats($user);
        $currentLevel = $stats instanceof CommunityUserStat ? $stats->level : (int) $user->level;

        while (true) {
            $nextLevel = $currentLevel + 1;
            if ($nextLevel > 300) {
                break;
            }

            $cumulative = $this->cumulativeExpForLevel($nextLevel);

            $currentXp = $stats instanceof CommunityUserStat ? $stats->xp : (int) $user->xp;
            if ($currentXp >= $cumulative) {
                $currentLevel++;
                if ($stats) {
                    $stats->update(['level' => $currentLevel]);
                } else {
                    $user->increment('level');
                }
                $user->setAttribute('level', $currentLevel);
                $user->notify(new GenericNotification('🎉', 'Chúc mừng! Bạn đã lên Level '.$nextLevel.'!', route('profile', $user->username ?? $user->id)));
                $leveled = true;
            } else {
                break;
            }
        }

        return $leveled;
    }

    private function communityStats(User $user): ?CommunityUserStat
    {
        return app(CommunityStatsService::class)->for($user);
    }

    private function referencePillar(Model $reference): ?string
    {
        if ($reference instanceof Post) {
            return $reference->pillar;
        }

        if ($reference instanceof Comment) {
            return $reference->post?->pillar;
        }

        return null;
    }

    public function expRequiredForLevel(int $level): int
    {
        $table = config('exp_table');
        if (isset($table[$level])) {
            return $table[$level];
        }

        return (int) round(158340 * pow(1.08, $level - 60));
    }

    public function cumulativeExpForLevel(int $level): int
    {
        $total = 0;
        for ($i = 1; $i < $level; $i++) {
            $total += $this->expRequiredForLevel($i);
        }

        return $total;
    }

    public function expToNextLevel(User $user): int
    {
        $stats = $this->communityStats($user);
        $level = $stats instanceof CommunityUserStat ? $stats->level : $user->level;
        $xp = $stats instanceof CommunityUserStat ? $stats->xp : $user->xp;
        $nextLevel = $level + 1;
        if ($nextLevel > 300) {
            return 0;
        }

        return max(0, $this->cumulativeExpForLevel($nextLevel) - $xp);
    }

    public function expProgressPct(User $user): float
    {
        $stats = $this->communityStats($user);
        $level = $stats instanceof CommunityUserStat ? $stats->level : $user->level;
        $xp = $stats instanceof CommunityUserStat ? $stats->xp : $user->xp;
        $currentLevelExp = $this->cumulativeExpForLevel($level);
        $nextLevelExp = $this->cumulativeExpForLevel($level + 1);
        $range = $nextLevelExp - $currentLevelExp;
        if ($range === 0) {
            return 100;
        }

        return min(100, max(0, round(($xp - $currentLevelExp) / $range * 100, 1)));
    }
}
