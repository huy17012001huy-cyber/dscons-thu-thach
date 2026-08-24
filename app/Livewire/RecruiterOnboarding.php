<?php

namespace App\Livewire;

use App\Models\RecruiterProfile;
use Illuminate\Support\Str;
use Livewire\Component;

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
        if (! $profile) return;
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

        $user = auth()->user();
        $user->update(['account_type' => 'recruiter']);
        RecruiterProfile::updateOrCreate(['brand_id' => brand()->id, 'user_id' => $user->id], [
            'company_name' => $data['companyName'],
            'company_slug' => Str::slug($data['companyName']).'-'.$user->id,
            'business_email' => $data['businessEmail'] ?: $user->email,
            'website' => $data['website'] ?: null,
            'industry' => $data['industry'],
            'description' => $data['description'] ?: null,
            'verification_status' => 'pending',
        ]);

        $this->redirect(community_route('recruiter.dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.recruiter-onboarding')->layout('layouts.recruiter', ['title' => 'Đăng ký nhà tuyển dụng']);
    }
}
