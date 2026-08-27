<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\CommunityApplication;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Modules\Community\Application\CommunityApplicationService;

class AdminCommunities extends Component
{
    public string $filter = 'pending';

    public string $reviewNote = '';

    public function approve(int $id, CommunityApplicationService $applications): void
    {
        $admin = $this->currentSuperAdmin();
        if (! $applications->approve($id, $admin, $this->reviewNote ?: null)) {
            return;
        }

        $this->reviewNote = '';
        $this->dispatch('toast', message: 'Đã duyệt và tạo cộng đồng.', type: 'success');
    }

    public function reject(int $id, CommunityApplicationService $applications): void
    {
        $admin = $this->currentSuperAdmin();
        if (! $applications->reject($id, $admin, $this->reviewNote ?: null)) {
            return;
        }
        $this->reviewNote = '';
        $this->dispatch('toast', message: 'Đã từ chối hồ sơ.', type: 'success');
    }

    public function render(): View
    {
        $applications = CommunityApplication::query()
            ->with('applicant:id,name,email')
            ->when($this->filter !== 'all', fn ($q) => $q->where('status', $this->filter))
            ->latest()->get();
        $communities = Brand::query()->withCount('users')->with('owner:id,name')->latest()->get();

        return view('livewire.admin-communities', compact('applications', 'communities'))
            ->layout('layouts.app', ['title' => 'Quản lý cộng đồng']);
    }

    private function currentSuperAdmin(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->isSuperAdmin(), 403);

        return $user;
    }
}
