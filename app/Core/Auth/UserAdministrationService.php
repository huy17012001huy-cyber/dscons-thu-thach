<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Core\Audit\AuditLogger;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class UserAdministrationService
{
    public function __construct(private readonly AuditLogger $audit) {}

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

    public function toggleSuperAdmin(User $actor, User $target): bool
    {
        $this->assertSuperAdmin($actor);
        if ($actor->is($target)) {
            return false;
        }

        return DB::transaction(function () use ($actor, $target): bool {
            $target = User::query()->lockForUpdate()->findOrFail($target->id);

            if ($target->isSuperAdmin() && User::query()->where('is_admin', true)->lockForUpdate()->count() <= 1) {
                return false;
            }

            $target->is_admin = ! $target->is_admin;
            $target->save();
            $action = $target->isSuperAdmin() ? 'super_admin_granted' : 'super_admin_revoked';
            DB::afterCommit(fn () => $this->audit->record('auth', $action, $actor, $target));

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
