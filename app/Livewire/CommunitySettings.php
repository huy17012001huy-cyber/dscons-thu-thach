<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\EngineerCv;
use App\Models\EngineerProfile;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CommunitySettings extends Component
{
    public Brand $community;
    public bool $hasCv = false;
    public bool $hasRecruitment = false;

    public function mount(): void
    {
        $this->community = brand();
        $this->authorizeCommunityAdmin();
        $this->hasCv = (bool) $this->community->has_cv;
        $this->hasRecruitment = (bool) $this->community->has_recruitment;
    }

    public function save(): void
    {
        $this->authorizeCommunityAdmin();

        $this->community->update([
            'has_cv' => $this->hasCv,
            'has_recruitment' => $this->hasRecruitment,
        ]);

        if (! $this->hasCv) {
            EngineerCv::query()->where('brand_id', $this->community->id)->update(['status' => 'draft']);
            EngineerProfile::query()->where('brand_id', $this->community->id)->update(['is_searchable' => false]);
        }

        $this->dispatch('toast', message: 'Đã lưu cài đặt community.', type: 'success');
    }

    private function authorizeCommunityAdmin(): void
    {
        abort_unless(Auth::user()?->isCommunityAdmin($this->community->id), 403);
    }

    public function render()
    {
        return view('livewire.community-settings')
            ->layout('layouts.app', ['title' => 'Cài đặt '.$this->community->name]);
    }
}
