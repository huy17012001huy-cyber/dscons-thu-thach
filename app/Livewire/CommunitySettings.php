<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\Community\Application\CommunityFeatureSettingsService;

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
        $this->community = app(CommunityFeatureSettingsService::class)->update(
            $this->community,
            $this->currentUser(),
            $this->hasCv,
            $this->hasRecruitment,
        );
        $this->dispatch('toast', message: 'Đã lưu cài đặt community.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.community-settings')
            ->layout('layouts.app', ['title' => 'Cài đặt '.$this->community->name]);
    }

    private function authorizeCommunityAdmin(): void
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
