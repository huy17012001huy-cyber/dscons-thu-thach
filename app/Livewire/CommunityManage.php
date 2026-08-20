<?php

namespace App\Livewire;

use App\Models\Brand;
use Illuminate\Support\Facades\Auth;
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

        $data = ['name' => trim($this->name), 'tagline' => trim($this->tagline) ?: null, 'description' => trim($this->description) ?: null];
        if ($this->logo) $data['logo_path'] = $this->logo->store('community/logos', 'public');
        if ($this->banner) $data['banner_path'] = $this->banner->store('community/banners', 'public');
        $this->community->update($data);
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
