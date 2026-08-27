<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\User;
use App\Support\CommunityContentDefaults;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Community\Application\CommunityProfileData;
use Modules\Community\Application\CommunityProfileService;

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
        $this->authorizeAdmin();
        $this->name = $this->community->name;
        $this->tagline = $this->community->tagline ?? '';
        $this->description = $this->community->description ?? '';
        $this->guideContent = $this->community->guide_content ?: CommunityContentDefaults::guide();
        $this->rulesContent = $this->community->rules_content ?: CommunityContentDefaults::rules();
    }

    public function save(): void
    {
        $this->authorizeAdmin();
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
        $logoPath = $this->logo ? $this->logo->store('community/logos', 'public') : ($this->removeLogo ? null : $oldLogo);
        $bannerPath = $this->banner ? $this->banner->store('community/banners', 'public') : ($this->removeBanner ? null : $oldBanner);
        $this->community = app(CommunityProfileService::class)->update($this->community, $this->currentUser(), new CommunityProfileData(
            name: trim($this->name),
            tagline: trim($this->tagline) ?: null,
            description: trim($this->description) ?: null,
            guideContent: trim($this->guideContent) ?: null,
            rulesContent: trim($this->rulesContent) ?: null,
            logoPath: $logoPath,
            bannerPath: $bannerPath,
        ));

        $this->deleteReplacedMedia($oldLogo, $logoPath);
        $this->deleteReplacedMedia($oldBanner, $bannerPath);
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

    private function deleteReplacedMedia(?string $oldPath, ?string $newPath): void
    {
        if ($oldPath && $oldPath !== $newPath && str_starts_with($oldPath, 'community/')) {
            Storage::disk('public')->delete($oldPath);
        }
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
