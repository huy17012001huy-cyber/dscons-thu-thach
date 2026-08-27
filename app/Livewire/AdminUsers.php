<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Core\Auth\UserAdministrationService;
use App\Core\Auth\UserProvisioningService;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Community\Application\CommunityMemberRoleService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminUsers extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    // ─── Tạo thành viên mới ───────────────────────────────
    public bool $showCreateModal = false;

    public string $newName = '';

    public string $newEmail = '';

    public string $newRole = 'member'; // member | mod | admin

    public function openCreateModal(): void
    {
        if (! Auth::user()?->is_admin) {
            return;
        }
        $this->reset(['newName', 'newEmail']);
        $this->newRole = 'member';
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function createUser(): void
    {
        if (! Auth::user()?->is_admin) {
            return;
        }

        $this->validate([
            'newName' => 'required|min:2|max:50',
            'newEmail' => 'required|email|unique:users,email',
            'newRole' => 'required|in:member,mod,admin',
        ], [], [
            'newName' => 'họ tên',
            'newEmail' => 'email',
            'newRole' => 'vai trò',
        ]);

        $user = app(UserProvisioningService::class)->provisionAdministratorMember(
            brand(),
            $this->newName,
            $this->newEmail,
            $this->newRole,
        );

        $this->showCreateModal = false;
        $this->reset(['newName', 'newEmail']);
        $this->resetPage();
        $this->dispatch('toast', message: 'Đã tạo thành viên '.$user->name, type: 'success');
    }

    public function toggleAdmin(int $id, UserAdministrationService $administration): void
    {
        $actor = Auth::user();
        abort_unless($actor instanceof User && $actor->isSuperAdmin(), 403);
        $user = User::findOrFail($id);
        if ($administration->toggleSuperAdmin($actor, $user)) {
            $this->dispatch('toast', message: 'Đã cập nhật quyền Super Admin cho '.$user->name, type: 'success');
        }
    }

    public function toggleModerator(int $id, CommunityMemberRoleService $roles): void
    {
        if (! Auth::user()?->is_admin) {
            return;
        }
        $user = User::findOrFail($id);
        if ($user->isSuperAdmin()) {
            return;
        }

        $currentRole = $user->communityRole(brand()->id);
        if ($currentRole === null) {
            return;
        }

        $roles->changeRole(brand(), Auth::user(), $user->id, $currentRole === 'moderator' ? 'member' : 'moderator');
    }

    public function banUser(int $id, UserAdministrationService $administration): void
    {
        $actor = Auth::user();
        abort_unless($actor instanceof User && $actor->isSuperAdmin(), 403);
        $user = User::findOrFail($id);
        if ($administration->ban($actor, $user)) {
            $this->dispatch('toast', message: $user->name.' đã bị ban', type: 'success');
        }
    }

    public function unbanUser(int $id, UserAdministrationService $administration): void
    {
        $actor = Auth::user();
        abort_unless($actor instanceof User && $actor->isSuperAdmin(), 403);
        $user = User::findOrFail($id);
        if ($administration->unban($actor, $user)) {
            $this->dispatch('toast', message: $user->name.' đã được mở khóa', type: 'success');
        }
    }

    public function exportCsv(): ?StreamedResponse
    {
        if (! Auth::user()?->is_admin) {
            return null;
        }

        $filename = 'members-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            if (! is_resource($handle)) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Họ tên', 'Email'], ',', '"', '', "\n");

            foreach ($this->usersQuery()->select(['name', 'email'])->orderBy('name')->cursor() as $user) {
                fputcsv($handle, [$user->name, $user->email], ',', '"', '', "\n");
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /** @return Builder<User> */
    private function usersQuery(): Builder
    {
        $query = User::query();

        if ($this->search) {
            $term = '%'.$this->search.'%';
            $query->where(fn ($q) => $q->where('name', 'ilike', $term)->orWhere('email', 'ilike', $term)->orWhere('username', 'ilike', $term));
        }

        return $query;
    }

    public function render(): View
    {
        $query = $this->usersQuery()
            ->with('membership')
            ->with(['brandRoles' => fn ($query) => $query->where('brands.id', brand()->id)])
            ->withCount('posts');

        return view('livewire.admin-users', ['users' => $query->latest()->paginate(20)])
            ->layout('layouts.app', ['title' => 'Quản lý người dùng — Admin']);
    }
}
