<?php

namespace App\Livewire;

use App\Models\EngineerCv;
use App\Models\EngineerProfile;
use App\Models\RecruiterPlan;
use App\Models\RecruiterProfile;
use App\Models\RecruitmentContactRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;

class AdminRecruitment extends Component
{
    public bool $isGlobalAdmin = false;

    public string $planName = '';

    public string $planDescription = '';

    public int $planCredits = 0;

    public ?int $planDuration = null;

    public int $planPrice = 0;

    public function mount(): void
    {
        $this->isGlobalAdmin = request()->routeIs('admin.*') && auth()->user()?->isSuperAdmin();
    }

    public function approve(int $profileId): void
    {
        $this->recruiterProfilesQuery()->findOrFail($profileId)->update(['verification_status' => 'verified', 'verified_at' => now(), 'reviewed_by' => auth()->id(), 'review_note' => null]);
    }

    public function reject(int $profileId): void
    {
        $this->recruiterProfilesQuery()->findOrFail($profileId)->update(['verification_status' => 'rejected', 'reviewed_by' => auth()->id()]);
    }

    public function savePlan(): void
    {
        $data = $this->validate(['planName' => 'required|string|max:120', 'planDescription' => 'nullable|string|max:500', 'planCredits' => 'integer|min:0|max:100000', 'planDuration' => 'nullable|integer|min:1|max:3650', 'planPrice' => 'integer|min:0|max:100000000']);
        RecruiterPlan::create(['name' => $data['planName'], 'description' => $data['planDescription'] ?: null, 'contact_credits' => $data['planCredits'], 'duration_days' => $data['planDuration'], 'price' => $data['planPrice'], 'is_active' => true]);
        $this->reset(['planName', 'planDescription', 'planCredits', 'planDuration', 'planPrice']);
    }

    public function togglePlan(int $planId): void
    {
        $plan = RecruiterPlan::findOrFail($planId);
        $plan->update(['is_active' => ! $plan->is_active]);
    }

    public function render(): View
    {
        $pendingRecruiters = $this->recruiterProfilesQuery()->with('user')
            ->where('verification_status', 'pending')
            ->latest()
            ->get();
        $verifiedRecruiters = $this->recruiterProfilesQuery()->with('user')
            ->where('verification_status', 'verified')
            ->latest('verified_at')
            ->get();

        $plans = $this->recruiterPlansQuery()->latest()->get();

        $connections = $this->contactRequestsQuery()
            ->with([
                'recruiter.recruiterProfile',
                'engineer.engineerProfile',
                'cv',
                'conversation',
            ])
            ->latest()
            ->get();

        $engineerCvs = $this->engineerCvsQuery()
            ->with('user')
            ->latest('updated_at')
            ->get();
        $cvProfiles = $this->engineerProfilesQuery()
            ->whereIn('user_id', $engineerCvs->pluck('user_id'))
            ->get()
            ->keyBy('user_id');

        return view('livewire.admin-recruitment', compact('pendingRecruiters', 'verifiedRecruiters', 'plans', 'connections', 'engineerCvs', 'cvProfiles'))
            ->layout('layouts.app', ['title' => 'Quản lý tuyển dụng']);
    }

    /** @return Builder<RecruiterProfile> */
    private function recruiterProfilesQuery(): Builder
    {
        return $this->isGlobalAdmin ? RecruiterProfile::withoutGlobalScopes() : RecruiterProfile::query();
    }

    /** @return Builder<RecruiterPlan> */
    private function recruiterPlansQuery(): Builder
    {
        return $this->isGlobalAdmin ? RecruiterPlan::withoutGlobalScopes() : RecruiterPlan::query();
    }

    /** @return Builder<RecruitmentContactRequest> */
    private function contactRequestsQuery(): Builder
    {
        return $this->isGlobalAdmin ? RecruitmentContactRequest::withoutGlobalScopes() : RecruitmentContactRequest::query();
    }

    /** @return Builder<EngineerCv> */
    private function engineerCvsQuery(): Builder
    {
        return $this->isGlobalAdmin ? EngineerCv::withoutGlobalScopes() : EngineerCv::query();
    }

    /** @return Builder<EngineerProfile> */
    private function engineerProfilesQuery(): Builder
    {
        return $this->isGlobalAdmin ? EngineerProfile::withoutGlobalScopes() : EngineerProfile::query();
    }
}
