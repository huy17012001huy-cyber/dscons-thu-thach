<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\User;
use App\Support\CommunityContentDefaults;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class CommunityManage extends Component
{
    use WithFileUploads;

    public Brand $community;
    public string $name = '';
    public string $tagline = '';
    public string $description = '';
    public string $guideContent = '';
    public string $rulesContent = '';
    public mixed $logo = null;
    public mixed $banner = null;
    public bool $removeLogo = false;
    public bool $removeBanner = false;

    public function mount(): void
    {
        $this->community = brand();
        $user = Auth::user();
        abort_unless($user instanceof User && $user->isCommunityAdmin($this->community->id), 403);
        $this->name = $this->community->name;
        $this->tagline = $this->community->tagline ?? '';
        $this->description = $this->community->description ?? '';
        $this->guideContent = $this->community->guide_content ?: CommunityContentDefaults::guide();
        $this->rulesContent = $this->community->rules_content ?: CommunityContentDefaults::rules();
    }

    public function save(): void
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->isCommunityAdmin($this->community->id), 403);
        $this->validate([
            'name' => 'required|string|max:100',
            'tagline' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'guideContent' => 'nullable|string|max:30000',
            'rulesContent' => 'nullable|string|max:30000',
            'logo' => 'nullable|image|max:4096',
            'banner' => 'nullable|image|max:8192',
        ]);

        $oldLogo = $this->community->logo_path;
        $oldBanner = $this->community->banner_path;
        $data = [
            'name' => trim($this->name),
            'tagline' => trim($this->tagline) ?: null,
            'description' => trim($this->description) ?: null,
            'guide_content' => trim($this->guideContent) ?: null,
            'rules_content' => trim($this->rulesContent) ?: null,
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

    public function render(): View
    {
        $members = $this->community->users()->withPivot('role')->latest('brand_user.created_at')->limit(20)->get();
        $plans = $this->community->membershipPlans()->orderByDesc('tier')->get();

        return view('livewire.community-manage', compact('members', 'plans'))
            ->layout('layouts.app', ['title' => 'Quản lý '.$this->community->name]);
    }
}
