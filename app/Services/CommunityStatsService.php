<?php

namespace App\Services;

use App\Models\CommunityUserStat;
use App\Models\User;

class CommunityStatsService
{
    public function brandId(): ?int
    {
        return app()->bound('brand') ? (int) brand()->id : null;
    }

    public function for(User $user): ?CommunityUserStat
    {
        $brandId = $this->brandId();
        if (!$brandId) {
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
