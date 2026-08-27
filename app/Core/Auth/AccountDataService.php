<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Models\Brand;
use App\Models\User;
use App\Models\UserBillingProfile;
use App\Models\UserCommunityPreference;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class AccountDataService
{
    /** @param array<string, string|null> $attributes */
    public function saveBilling(User $user, string $type, array $attributes): UserBillingProfile
    {
        if (! in_array($type, ['personal', 'company'], true)) {
            throw new AuthorizationException('Invalid billing profile type.');
        }

        return DB::transaction(fn (): UserBillingProfile => UserBillingProfile::updateOrCreate(
            ['user_id' => $user->id, 'type' => $type],
            [...$attributes, 'user_id' => $user->id, 'type' => $type],
        ));
    }

    public function toggleNotifications(User $user, Brand $community): bool
    {
        if (! $user->is_admin && ! $user->brandRoles()->whereKey($community->id)->exists()) {
            throw new AuthorizationException('You are not a member of this community.');
        }

        return DB::transaction(function () use ($user, $community): bool {
            $preference = UserCommunityPreference::withoutGlobalScopes()->firstOrCreate(
                ['user_id' => $user->id, 'brand_id' => $community->id],
                ['notifications_enabled' => true],
            );
            $preference->update(['notifications_enabled' => ! $preference->notifications_enabled]);

            return (bool) $preference->notifications_enabled;
        });
    }
}
