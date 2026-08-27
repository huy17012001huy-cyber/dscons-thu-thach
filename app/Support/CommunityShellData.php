<?php

namespace App\Support;

use App\Models\Brand;
use App\Models\Course;
use App\Models\Expedition;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class CommunityShellData
{
    /** @return array<string, mixed> */
    public static function make(Brand $brand, ?User $user, bool $isDiscovery): array
    {
        $brandId = (int) $brand->id;
        $memberships = self::membershipsForUser($user);
        $currentMembership = self::currentMembership($user, $brandId);
        $isPremiumMember = $user?->hasPremiumMembership($brandId) ?? false;
        $isCommunityParticipant = $user?->isCommunityParticipant($brandId) ?? false;
        $membershipLabel = self::membershipLabel($currentMembership, $brand);
        $communityMembers = $user
            ? $brand->users()->limit(6)->get(['users.id', 'users.name', 'users.avatar'])
            : collect();

        return [
            'isDiscoveryContext' => $isDiscovery,
            'sidebarMemberships' => $memberships,
            'discoveryMemberships' => $memberships,
            'freezeExpedition' => self::freezeExpedition($brandId),
            'currentMembership' => $currentMembership,
            'isPremiumMember' => $isPremiumMember,
            'isCommunityParticipant' => $isCommunityParticipant,
            'membershipLabel' => $membershipLabel,
            'memberAvatarSize' => CommunityBrandSettings::memberAvatarSize($brand),
            'featuredUpgrade' => $user ? self::featuredCourse($brandId) : null,
            'communityMembers' => $communityMembers,
            'communityMemberCount' => $user ? $brand->users()->count() : 0,
            'communityActiveCount' => $user ? $brand->users()->where('last_active_at', '>=', now()->subMinutes(30))->count() : 0,
            'communityAdminCount' => $user ? $brand->users()->wherePivotIn('role', ['owner', 'admin'])->count() : 0,
        ];
    }

    /** @return Collection<int, Membership> */
    private static function membershipsForUser(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        $memberships = $user->memberships()
            ->withoutGlobalScopes()
            ->with('brand')
            ->whereIn('status', ['active', 'trial'])
            ->get()
            ->filter(fn (Membership $membership): bool => $membership->brand instanceof Brand);

        $roleBrands = $user->brandRoles()
            ->whereIn('brand_user.role', ['member', 'moderator', 'admin', 'owner'])
            ->get();

        $roleMemberships = $roleBrands->map(function (Brand $brand): Membership {
            $membership = new Membership([
                'brand_id' => $brand->id,
                'tier' => 'free',
                'status' => 'active',
            ]);

            return $membership->setRelation('brand', $brand);
        });

        return $memberships->merge($roleMemberships)->unique('brand_id')->values();
    }

    private static function currentMembership(?User $user, int $brandId): ?Membership
    {
        return $user?->memberships()
            ->withoutGlobalScopes()
            ->where('brand_id', $brandId)
            ->latest()
            ->first();
    }

    private static function membershipLabel(?Membership $membership, Brand $brand): string
    {
        $fallback = CommunityBrandSettings::membershipLabel($brand);

        if (! $membership?->isPremium()) {
            return $fallback;
        }

        if (preg_match('/^community-(\d+)$/', (string) $membership->plan, $matches)) {
            $planName = MembershipPlan::withoutGlobalScopes()
                ->where('brand_id', $brand->id)
                ->whereKey((int) $matches[1])
                ->value('name');

            if (filled($planName) && mb_strtolower($planName) !== 'premium') {
                return $planName;
            }
        }

        return $fallback;
    }

    private static function featuredCourse(int $brandId): ?Course
    {
        return Course::withoutGlobalScopes()
            ->where('brand_id', $brandId)
            ->where('is_published', true)
            ->latest()
            ->first();
    }

    private static function freezeExpedition(int $brandId): ?Expedition
    {
        return Cache::remember("layout_freeze_banner_b{$brandId}_v2", 30, function () use ($brandId) {
            return Expedition::withoutGlobalScopes()
                ->where('brand_id', $brandId)
                ->where('status', 'active')
                ->whereNotNull('freeze_from_day')
                ->whereNotNull('freeze_ends_at')
                ->where('freeze_ends_at', '>', now())
                ->first();
        });
    }
}
