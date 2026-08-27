<?php

declare(strict_types=1);

namespace Modules\Community\Application;

use App\Core\CommunityContext;
use App\Models\Brand;
use App\Models\CommunityRoleAudit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CommunityMemberRoleService
{
    private const ROLES = ['member', 'moderator', 'admin'];

    public function __construct(private readonly CommunityContext $context) {}

    public function changeRole(Brand $community, User $actor, int $targetId, string $role): void
    {
        $this->assertCurrentCommunity($community);
        abort_unless($actor->isCommunityAdmin($community->id), 403);
        abort_unless(in_array($role, self::ROLES, true), 422);

        $target = User::query()->findOrFail($targetId);
        abort_if($target->isSuperAdmin(), 403, 'Không thể thay đổi quyền Super Admin.');
        $currentRole = $target->communityRole($community->id);
        abort_unless($currentRole !== null, 404);
        abort_if($currentRole === 'owner', 403, 'Chỉ Owner mới có thể chuyển quyền sở hữu.');
        abort_if($target->id === $actor->id && $actor->isCommunityOwner($community->id), 422, 'Owner không thể tự hạ quyền.');

        if (($role === 'admin' || $currentRole === 'admin') && ! $actor->isCommunityOwner($community->id)) {
            abort(403, 'Chỉ Owner mới được quản lý Admin community.');
        }

        DB::transaction(function () use ($community, $actor, $target, $currentRole, $role): void {
            $community->users()->syncWithoutDetaching([$target->id => ['role' => $role]]);
            CommunityRoleAudit::create([
                'brand_id' => $community->id,
                'actor_id' => $actor->id,
                'user_id' => $target->id,
                'from_role' => $currentRole,
                'to_role' => $role,
                'action' => 'role_changed',
            ]);
        });
    }

    public function transferOwnership(Brand $community, User $currentOwner, int $targetId): void
    {
        $this->assertCurrentCommunity($community);
        abort_unless($currentOwner->isCommunityOwner($community->id), 403);

        $target = User::query()->findOrFail($targetId);
        abort_if($target->isSuperAdmin(), 403, 'Không thể chuyển quyền cho Super Admin.');
        abort_if($target->id === $currentOwner->id, 422, 'Bạn đã là Owner của community này.');
        $targetRole = $target->communityRole($community->id);
        abort_unless($targetRole !== null, 404);

        DB::transaction(function () use ($community, $currentOwner, $target, $targetRole): void {
            $community->update(['owner_id' => $target->id]);
            $community->users()->syncWithoutDetaching([
                $currentOwner->id => ['role' => 'admin'],
                $target->id => ['role' => 'owner'],
            ]);
            CommunityRoleAudit::insert([
                [
                    'brand_id' => $community->id,
                    'actor_id' => $currentOwner->id,
                    'user_id' => $currentOwner->id,
                    'from_role' => 'owner',
                    'to_role' => 'admin',
                    'action' => 'ownership_transferred',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'brand_id' => $community->id,
                    'actor_id' => $currentOwner->id,
                    'user_id' => $target->id,
                    'from_role' => $targetRole,
                    'to_role' => 'owner',
                    'action' => 'ownership_received',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        });
    }

    private function assertCurrentCommunity(Brand $community): void
    {
        abort_unless($this->context->require()->is($community), 403);
    }
}
