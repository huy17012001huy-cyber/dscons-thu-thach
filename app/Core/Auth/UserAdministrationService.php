<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class UserAdministrationService
{
    public function ban(User $actor, User $target): bool
    {
        $this->assertSuperAdmin($actor);
        if ($actor->is($target)) {
            return false;
        }

        return DB::transaction(function () use ($target): bool {
            $membership = $target->membership;
            if (! $membership) {
                return false;
            }
            $membership->update(['status' => 'banned']);

            return true;
        });
    }

    public function unban(User $actor, User $target): bool
    {
        $this->assertSuperAdmin($actor);

        return DB::transaction(function () use ($target): bool {
            $membership = $target->membership;
            if (! $membership || $membership->status !== 'banned') {
                return false;
            }
            $membership->update(['status' => 'active']);

            return true;
        });
    }

    private function assertSuperAdmin(User $actor): void
    {
        if (! $actor->isSuperAdmin()) {
            throw new AuthorizationException('Super admin access is required.');
        }
    }
}
