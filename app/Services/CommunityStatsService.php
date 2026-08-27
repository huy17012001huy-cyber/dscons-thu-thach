<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\CommunityContext;
use App\Models\CommunityUserStat;
use App\Models\User;

class CommunityStatsService
{
    public function __construct(private readonly CommunityContext $context) {}

    public function brandId(): ?int
    {
        return $this->context->current()?->id;
    }

    public function for(User $user): ?CommunityUserStat
    {
        $brandId = $this->brandId();
        if (!$brandId) {
            return null;
        }

        // A host-level brand can be bound for every request, including
        // onboarding, admin, CLI and legacy records. Only switch a user to
        // community-scoped stats after they actually belong to this brand.
        $belongsToBrand = $user->memberships()
            ->withoutGlobalScopes()
            ->where('brand_id', $brandId)
            ->exists();
        if (!$belongsToBrand) {
            return null;
        }

        return CommunityUserStat::firstOrCreate(
            ['brand_id' => $brandId, 'user_id' => $user->id],
            [
                'xp' => (int) ($user->getRawOriginal('xp') ?? 0),
                'level' => max(1, (int) ($user->getRawOriginal('level') ?? 1)),
                'aip' => (int) ($user->getRawOriginal('aip') ?? 0),
                'streak' => (int) ($user->getRawOriginal('streak') ?? 0),
                'last_active_at' => $user->getRawOriginal('last_active_at'),
            ]
        );
    }

    public function syncUser(User $user): ?CommunityUserStat
    {
        $stats = $this->for($user);
        if (!$stats) {
            return null;
        }

        $user->setAttribute('xp', (int) $stats->xp);
        $user->setAttribute('level', (int) $stats->level);
        $user->setAttribute('aip', (int) $stats->aip);
        $user->setAttribute('streak', (int) $stats->streak);
        $user->setAttribute('last_active_at', $stats->last_active_at);

        if (!$stats->last_active_at || $stats->last_active_at->lt(now()->subMinutes(5))) {
            $stats->forceFill(['last_active_at' => now()])->save();
            $user->setAttribute('last_active_at', $stats->last_active_at);
        }

        return $stats;
    }
}
