<?php

declare(strict_types=1);

namespace App\Core\Gamification;

use App\Models\Comment;
use App\Models\CommunityUserStat;
use App\Models\Post;
use App\Models\User;
use App\Models\XpTransaction;
use App\Notifications\GenericNotification;
use Illuminate\Database\Eloquent\Model;

final class XpService
{
    public const REWARDS = [
        'post_liked' => 2,
        'post_commented' => 3,
        'post_bookmarked' => 1,
        'comment_liked' => 1,
        'best_answer' => 25,
        'cot' => 50,
        'post' => 0,
        'comment' => 1,
        'login' => 1,
        'expedition_checkin' => 5,
        'expedition_complete' => 100,
        'expedition_captain' => 200,
        'lesson_complete' => 10,
        'course_complete' => 50,
        'challenge' => 25,
        'affiliate' => 100,
        'quiz_correct' => 2,
    ];

    public function __construct(
        private readonly BadgeService $badges,
        private readonly CommunityStatsService $stats,
        private readonly PowerSymbolService $powerSymbols,
    ) {}

    public function award(User $user, string $type, float $multiplier = 1.0, ?string $description = null, ?Model $reference = null): int
    {
        $base = self::REWARDS[$type] ?? 0;
        if ($base === 0) {
            return 0;
        }

        $stats = $this->stats->for($user);
        $streakMultiplier = $this->streakMultiplier($stats instanceof CommunityUserStat ? $stats->streak : $user->streak);
        $total = (int) round($base * $multiplier * $streakMultiplier);

        XpTransaction::create([
            'user_id' => $user->id,
            'amount' => $total,
            'type' => $type,
            'reference_type' => $reference ? $reference::class : null,
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
        $user->forceFill(['xp' => (int) $user->getAttribute('xp'), 'level' => (int) $user->getAttribute('level')])->saveQuietly();
        $this->awardPowerSymbol($user, $type, $reference);
        $this->badges->check($user);

        return $total;
    }

    public function checkLevelUp(User $user): bool
    {
        $stats = $this->stats->for($user);
        $currentLevel = $stats instanceof CommunityUserStat ? $stats->level : (int) $user->level;
        $leveled = false;

        while ($currentLevel < 300 && $this->currentXp($user, $stats) >= $this->cumulativeExpForLevel($currentLevel + 1)) {
            $currentLevel++;
            $stats ? $stats->update(['level' => $currentLevel]) : $user->increment('level');
            $user->setAttribute('level', $currentLevel);
            $user->notify(new GenericNotification('🎉', 'Chúc mừng! Bạn đã lên Level '.$currentLevel.'!', route('profile', $user->username ?? $user->id)));
            $leveled = true;
        }

        return $leveled;
    }

    public function expRequiredForLevel(int $level): int
    {
        return isset(config('exp_table')[$level])
            ? (int) config('exp_table')[$level]
            : (int) round(158340 * pow(1.08, $level - 60));
    }

    public function cumulativeExpForLevel(int $level): int
    {
        $total = 0;
        for ($index = 1; $index < $level; $index++) {
            $total += $this->expRequiredForLevel($index);
        }

        return $total;
    }

    public function expToNextLevel(User $user): int
    {
        $stats = $this->stats->for($user);
        $level = $stats instanceof CommunityUserStat ? $stats->level : $user->level;
        if ($level >= 300) {
            return 0;
        }

        return max(0, $this->cumulativeExpForLevel($level + 1) - $this->currentXp($user, $stats));
    }

    public function expProgressPct(User $user): float
    {
        $stats = $this->stats->for($user);
        $level = $stats instanceof CommunityUserStat ? $stats->level : $user->level;
        $range = $this->cumulativeExpForLevel($level + 1) - $this->cumulativeExpForLevel($level);
        if ($range === 0) {
            return 100;
        }

        return min(100, max(0, round(($this->currentXp($user, $stats) - $this->cumulativeExpForLevel($level)) / $range * 100, 1)));
    }

    private function streakMultiplier(int $streak): float
    {
        return match (true) {
            $streak >= 90 => 1.5,
            $streak >= 30 => 1.2,
            $streak >= 7 => 1.1,
            default => 1.0,
        };
    }

    private function currentXp(User $user, ?CommunityUserStat $stats): int
    {
        return $stats instanceof CommunityUserStat ? (int) $stats->xp : (int) $user->xp;
    }

    private function awardPowerSymbol(User $user, string $type, ?Model $reference): void
    {
        if (! $reference || ! in_array($type, ['cot', 'post_liked', 'post_commented'], true)) {
            return;
        }

        $pillar = $reference instanceof Post ? $reference->pillar : ($reference instanceof Comment ? $reference->post?->pillar : null);
        if ($pillar) {
            $this->powerSymbols->addFragments($user, $pillar, $type === 'cot' ? 3 : 1);
        }
    }
}
