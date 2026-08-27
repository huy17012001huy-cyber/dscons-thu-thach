<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\DigitalProduct;
use App\Models\ToolInstallation;
use App\Services\ToolLicenseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class RevitDevicePage extends Component
{
    public function logoutDevice(ToolLicenseService $licenses): void
    {
        $installation = $this->installation();
        if (! $installation) {
            return;
        }
        $licenses->revokeInstallation($installation, 'user_logout');
        $this->dispatch('toast', message: 'Đã đăng xuất thiết bị Revit. Bạn có thể kích hoạt máy mới.', type: 'success');
    }

    public function render(): View
    {
        $brand = Brand::query()->where('slug', 'dscons')->firstOrFail();
        $installation = $this->installation();
        $tools = DigitalProduct::withoutGlobalScopes()
            ->where('brand_id', $brand->id)
            ->where('product_kind', 'revit_tool')
            ->where('is_published', true)
            ->with(['purchases' => fn ($query) => $query->withoutGlobalScopes()->where('user_id', Auth::id())->where('status', 'active')])
            ->get();

        return view('livewire.revit-device-page', compact('installation', 'tools'))
            ->layout('layouts.app', ['title' => 'Thiết bị Revit — DSCons']);
    }

    private function installation(): ?ToolInstallation
    {
        return ToolInstallation::withoutGlobalScopes()
            ->where('brand_id', Brand::query()->where('slug', 'dscons')->value('id'))
            ->where('user_id', Auth::id())
            ->where('platform', ToolLicenseService::PLATFORM)
            ->first();
    }
}
