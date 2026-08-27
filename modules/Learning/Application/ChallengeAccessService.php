<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\User;

final class ChallengeAccessService
{
    public function find(string $slug): Expedition
    {
        return Expedition::query()
            ->where('brand_id', brand()->id)
            ->where(function ($query) use ($slug): void {
                $query->where('slug', $slug)
                    ->orWhere('id', is_numeric($slug) ? (int) $slug : 0);
            })
            ->firstOrFail();
    }

    public function isPremiumLocked(Expedition $challenge, ?User $user): bool
    {
        if (($challenge->access_tier ?? 'premium') !== 'premium' || ! $user) {
            return false;
        }

        if ($user->isBrandAdmin() || $user->hasPremiumMembership(brand()->id)) {
            return false;
        }

        return ! $this->hasApprovedMembership($challenge, $user);
    }

    public function canAccessDay(Expedition $challenge, int $day, ?User $user): bool
    {
        if ($user?->isBrandAdmin()) {
            return true;
        }

        if (! $user || $day < 1 || $day > $challenge->required_days) {
            return false;
        }

        $member = $challenge->members()
            ->where('user_id', $user->id)
            ->whereIn('status', ['approved', 'paid'])
            ->whereNull('kicked_at')
            ->first();

        return $member instanceof ExpeditionMember
            && $member->personal_starts_at !== null
            && $day <= $challenge->getCurrentDayForMember($member);
    }

    private function hasApprovedMembership(Expedition $challenge, User $user): bool
    {
        return $challenge->members()
            ->where('user_id', $user->id)
            ->whereIn('status', ['approved', 'paid'])
            ->whereNull('kicked_at')
            ->exists();
    }
}
