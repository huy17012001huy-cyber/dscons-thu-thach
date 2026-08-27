<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Community\Application\CommunityMemberRoleService;

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
        app(CommunityMemberRoleService::class)->changeRole($this->community, $this->currentUser(), $userId, $role);
        $this->dispatch('toast', message: 'Đã cập nhật vai trò thành viên.', type: 'success');
    }

    public function transferOwnership(int $userId): void
    {
        $this->authorizeAdmin();
        app(CommunityMemberRoleService::class)->transferOwnership($this->community, $this->currentUser(), $userId);
        $this->community->refresh();
        $this->dispatch('toast', message: 'Đã chuyển quyền Owner.', type: 'success');
    }

    public function render(): View
    {
        $members = $this->community->users()->withPivot('role')->orderBy('name')->paginate(20);

        return view('livewire.community-members', compact('members'))
            ->layout('layouts.app', ['title' => 'Thành viên '.$this->community->name]);
    }

    private function authorizeAdmin(): void
    {
        abort_unless($this->currentUser()->isCommunityAdmin($this->community->id), 403);
    }

    private function currentUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        return $user;
    }
}
