<?php

namespace App\Livewire;

use App\Models\Brand;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class CommunityManage extends Component
{
    use WithFileUploads;

    public Brand $community;
    public string $name = '';
    public string $tagline = '';
    public string $description = '';
    public $logo;
    public $banner;
    public bool $removeLogo = false;
    public bool $removeBanner = false;

    public function mount(): void
    {
        $this->community = brand();
        abort_unless(Auth::user()?->isBrandAdmin($this->community->id), 403);
        $this->name = $this->community->name;
        $this->tagline = $this->community->tagline ?? '';
        $this->description = $this->community->description ?? '';
    }

    public function save(): void
    {
        abort_unless(Auth::user()?->isBrandAdmin($this->community->id), 403);
        $this->validate([
            'name' => 'required|string|max:100',
            'tagline' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'logo' => 'nullable|image|max:4096',
            'banner' => 'nullable|image|max:8192',
        ]);

        $oldLogo = $this->community->logo_path;
        $oldBanner = $this->community->banner_path;
        $data = [
            'name' => trim($this->name),
            'tagline' => trim($this->tagline) ?: null,
            'description' => trim($this->description) ?: null,
        ];

        if ($this->logo) {
            $data['logo_path'] = $this->logo->store('community/logos', 'public');
        } elseif ($this->removeLogo) {
            $data['logo_path'] = null;
        }

        if ($this->banner) {
            $data['banner_path'] = $this->banner->store('community/banners', 'public');
        } elseif ($this->removeBanner) {
            $data['banner_path'] = null;
        }

        $this->community->update($data);

        foreach ([
            [$oldLogo, $data['logo_path'] ?? $oldLogo],
            [$oldBanner, $data['banner_path'] ?? $oldBanner],
        ] as [$oldPath, $newPath]) {
            if ($oldPath && $oldPath !== $newPath && str_starts_with($oldPath, 'community/')) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $this->community->refresh();
        $this->reset(['logo', 'banner', 'removeLogo', 'removeBanner']);
        $this->dispatch('toast', message: 'Đã cập nhật cộng đồng.', type: 'success');
    }

    public function render()
    {
        $members = $this->community->users()->withPivot('role')->latest('brand_user.created_at')->limit(20)->get();
        $plans = $this->community->membershipPlans()->orderByDesc('tier')->get();

        return view('livewire.community-manage', compact('members', 'plans'))
            ->layout('layouts.app', ['title' => 'Quản lý '.$this->community->name]);
    }
}
