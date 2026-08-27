<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\ToolInstallation;
use App\Models\ToolSecurityEvent;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Modules\RevitTools\Application\ToolLicenseService;

class AdminToolLicenses extends Component
{
    public string $search = '';

    public function resetDevice(int $installationId, ToolLicenseService $licenses): void
    {
        $installation = ToolInstallation::withoutGlobalScopes()->where('brand_id', $this->brandId())->findOrFail($installationId);
        $licenses->resetInstallation($installation, $this->currentUser());
        $this->dispatch('toast', message: 'Đã reset thiết bị. Người dùng có thể kích hoạt lại một máy.', type: 'success');
    }

    public function revokeDevice(int $installationId, ToolLicenseService $licenses): void
    {
        $installation = ToolInstallation::withoutGlobalScopes()->where('brand_id', $this->brandId())->findOrFail($installationId);
        $licenses->revokeInstallation($installation, 'admin_revoke');
        $this->dispatch('toast', message: 'Đã thu hồi license thiết bị.', type: 'success');
    }

    public function unlockDevice(int $installationId, ToolLicenseService $licenses): void
    {
        $installation = ToolInstallation::withoutGlobalScopes()->where('brand_id', $this->brandId())->findOrFail($installationId);
        $licenses->unlockInstallation($installation, $this->currentUser());
        $this->dispatch('toast', message: 'Đã mở khóa và reset thiết bị. Người dùng có thể kích hoạt lại một máy.', type: 'success');
    }

    public function render(): View
    {
        $brandId = $this->brandId();
        $installations = ToolInstallation::withoutGlobalScopes()->with('user')
            ->where('brand_id', $brandId)
            ->when($this->search, fn ($q) => $q->whereHas('user', fn ($user) => $user->where('name', 'ilike', '%'.$this->search.'%')->orWhere('email', 'ilike', '%'.$this->search.'%')))
            ->latest('last_seen_at')->get();
        $events = ToolSecurityEvent::withoutGlobalScopes()->with(['user', 'installation'])
            ->where('brand_id', $brandId)->latest()->limit(20)->get();

        return view('livewire.admin-tool-licenses', compact('installations', 'events'))->layout('layouts.app', ['title' => 'License Revit — Admin']);
    }

    private function brandId(): int
    {
        return Brand::query()->where('slug', 'dscons')->firstOrFail()->id;
    }

    private function currentUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        return $user;
    }
}
