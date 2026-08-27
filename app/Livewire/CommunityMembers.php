<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\CommunityRoleAudit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CommunityMembers extends Component
{
    use WithPagination;

    public Brand $community;

    public function mount(): void
    {
        $this->community = brand();
        $this->authorizeAdmin();
    }

    public function changeRole(int $userId, string $role): void
    {
        $this->authorizeAdmin();
        abort_unless(in_array($role, ['member', 'moderator', 'admin'], true), 422);

        $actor = Auth::user();
        abort_unless($actor instanceof User, 403);
        $target = User::query()->findOrFail($userId);
        abort_if($target->isSuperAdmin(), 403, 'Không thể thay đổi quyền Super Admin.');

        $currentRole = $target->communityRole($this->community->id);
        abort_if($currentRole === 'owner', 403, 'Chỉ Owner mới có thể chuyển quyền sở hữu.');
        abort_if($target->id === $actor->id && $actor->isCommunityOwner($this->community->id), 422, 'Owner không thể tự hạ quyền.');

        $actorRole = $actor->communityRole($this->community->id);
        if (($role === 'admin' || $currentRole === 'admin') && $actorRole !== 'owner') {
            abort(403, 'Chỉ Owner mới được quản lý Admin community.');
        }

        DB::transaction(function () use ($target, $currentRole, $role): void {
            $this->community->users()->syncWithoutDetaching([
                $target->id => ['role' => $role],
            ]);

            CommunityRoleAudit::create([
                'brand_id' => $this->community->id,
                'actor_id' => Auth::id(),
                'user_id' => $target->id,
                'from_role' => $currentRole,
                'to_role' => $role,
                'action' => 'role_changed',
            ]);
        });

        $this->dispatch('toast', message: 'Đã cập nhật vai trò thành viên.', type: 'success');
    }

    public function transferOwnership(int $userId): void
    {
        $currentOwner = Auth::user();
        abort_unless($currentOwner instanceof User && $currentOwner->isCommunityOwner($this->community->id), 403);

        $target = User::query()->findOrFail($userId);
        abort_if($target->isSuperAdmin(), 403, 'Không thể chuyển quyền cho Super Admin.');
        abort_if($target->id === Auth::id(), 422, 'Bạn đã là Owner của community này.');

        DB::transaction(function () use ($target, $currentOwner): void {
            $this->community->update(['owner_id' => $target->id]);
            $this->community->users()->syncWithoutDetaching([
                $currentOwner->id => ['role' => 'admin'],
                $target->id => ['role' => 'owner'],
            ]);

            CommunityRoleAudit::insert([
                [
                    'brand_id' => $this->community->id,
                    'actor_id' => Auth::id(),
                    'user_id' => $currentOwner->id,
                    'from_role' => 'owner',
                    'to_role' => 'admin',
                    'action' => 'ownership_transferred',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'brand_id' => $this->community->id,
                    'actor_id' => Auth::id(),
                    'user_id' => $target->id,
                    'from_role' => $target->communityRole($this->community->id),
                    'to_role' => 'owner',
                    'action' => 'ownership_received',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        });

        $this->community->refresh();
        $this->dispatch('toast', message: 'Đã chuyển quyền Owner.', type: 'success');
    }

    private function authorizeAdmin(): void
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->isCommunityAdmin($this->community->id), 403);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $members = $this->community->users()
            ->withPivot('role')
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.community-members', compact('members'))
            ->layout('layouts.app', ['title' => 'Thành viên '.$this->community->name]);
    }
}
