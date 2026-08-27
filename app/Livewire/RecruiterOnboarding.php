<?php

namespace App\Livewire;

use App\Models\RecruiterProfile;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Component;
use Modules\Recruitment\Application\RecruiterOnboardingService;

class RecruiterOnboarding extends Component
{
    public string $companyName = '';

    public string $businessEmail = '';

    public string $website = '';

    public string $industry = 'BIM/MEP';

    public string $description = '';

    public function mount(): void
    {
        $profile = RecruiterProfile::where('user_id', auth()->id())->first();
        if (! $profile) {
            return;
        }
        $this->fill([
            'companyName' => $profile->company_name,
            'businessEmail' => $profile->business_email ?? '',
            'website' => $profile->website ?? '',
            'industry' => $profile->industry ?? 'BIM/MEP',
            'description' => $profile->description ?? '',
        ]);
    }

    public function save(): void
    {
        $data = $this->validate([
            'companyName' => 'required|string|max:160',
            'businessEmail' => 'nullable|email|max:180',
            'website' => 'nullable|url|max:180',
            'industry' => 'required|string|max:120',
            'description' => 'nullable|string|max:2000',
        ]);

        $user = $this->currentUser();
        app(RecruiterOnboardingService::class)->save(brand(), $user, [
            'companyName' => $data['companyName'],
            'businessEmail' => $data['businessEmail'] ?: null,
            'website' => $data['website'] ?: null,
            'industry' => $data['industry'],
            'description' => $data['description'] ?: null,
        ]);

        $this->redirect(community_route('recruiter.dashboard'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.recruiter-onboarding')->layout('layouts.recruiter', ['title' => 'Đăng ký nhà tuyển dụng']);
    }

    private function currentUser(): User
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        return $user;
    }
}
