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

    private function authorizeAdmin(): void
    {
        abort_unless(Auth::user()?->isCommunityAdmin($this->community->id), 403);
    }

    public function render()
    {
        $members = $this->community->users()
            ->withPivot('role')
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.community-members', compact('members'))
            ->layout('layouts.app', ['title' => 'Thành viên '.$this->community->name]);
    }
}
