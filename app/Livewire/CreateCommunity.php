<?php

namespace App\Livewire;

use App\Models\CommunityApplication;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class CreateCommunity extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $slug = '';

    public string $tagline = '';

    public string $description = '';

    public string $teachingTopic = '';

    public string $programDescription = '';

    public string $proposedPremiumPrice = '';

    public string $proposedSepayAccount = '';

    public string $proposedSepayBank = '';

    public ?TemporaryUploadedFile $logo = null;

    public ?TemporaryUploadedFile $banner = null;

    public function updatedName(string $value): void
    {
        if ($this->slug === '' || $this->slug === Str::slug($this->name)) {
            $this->slug = Str::slug($value);
        }
    }

    public function submit(): void
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        $this->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|alpha_dash:ascii|max:50|unique:brands,slug|unique:community_applications,slug',
            'tagline' => 'nullable|string|max:255',
            'description' => 'required|string|max:5000',
            'teachingTopic' => 'required|string|max:255',
            'programDescription' => 'required|string|max:5000',
            'proposedPremiumPrice' => 'nullable|integer|min:0|max:100000000',
            'proposedSepayAccount' => 'nullable|string|max:100',
            'proposedSepayBank' => 'nullable|string|max:100',
            'logo' => 'nullable|image|max:4096',
            'banner' => 'nullable|image|max:8192',
        ]);

        $data = [
            'applicant_id' => $user->id,
            'name' => trim($this->name),
            'slug' => Str::slug($this->slug),
            'tagline' => trim($this->tagline) ?: null,
            'description' => trim($this->description),
            'teaching_topic' => trim($this->teachingTopic),
            'program_description' => trim($this->programDescription),
            'proposed_premium_price' => $this->proposedPremiumPrice !== '' ? (int) $this->proposedPremiumPrice : null,
            'proposed_sepay_account' => trim($this->proposedSepayAccount) ?: null,
            'proposed_sepay_bank' => trim($this->proposedSepayBank) ?: null,
            'status' => 'pending',
        ];

        if ($this->logo) {
            $path = $this->logo->store('community/logos', 'public');
            if (is_string($path)) {
                $data['logo_path'] = $path;
            }
        }
        if ($this->banner) {
            $path = $this->banner->store('community/banners', 'public');
            if (is_string($path)) {
                $data['banner_path'] = $path;
            }
        }

        CommunityApplication::create($data);
        session()->flash('toast', ['message' => 'Đã gửi hồ sơ. Đội ngũ nền tảng sẽ xem xét và phản hồi cho bạn.', 'type' => 'success']);
        $this->redirect(route('communities'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.create-community')
            ->layout('layouts.app', ['title' => 'Tạo cộng đồng']);
    }
}
