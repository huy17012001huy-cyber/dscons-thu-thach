<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Core\Audit\AuditLogger;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

final class AdminSessionService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @return Collection<int, stdClass> */
    public function activeFor(User $user): Collection
    {
        $this->assertSuperAdmin($user);

        return DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get(['id', 'ip_address', 'user_agent', 'last_activity']);
    }

    public function revokeOther(User $user, string $currentSessionId): int
    {
        $this->assertSuperAdmin($user);

        return DB::transaction(function () use ($user, $currentSessionId): int {
            $revoked = DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', $currentSessionId)
                ->delete();

            if ($revoked > 0) {
                DB::afterCommit(fn () => $this->audit->record('auth', 'admin_sessions_revoked', $user, metadata: ['count' => $revoked]));
            }

            return $revoked;
        });
    }

    private function assertSuperAdmin(User $user): void
    {
        if (! $user->isSuperAdmin()) {
            throw new AuthorizationException('Super admin access is required.');
        }
    }
}
