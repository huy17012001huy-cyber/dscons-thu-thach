<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\MembershipPlan;

class MembershipPricing extends Component
{
    public const PLANS = [
        1  => ['weeks' => 1,  'price_per_week' => 500000, 'label' => '1 tuần',   'save' => 0],
        4  => ['weeks' => 4,  'price_per_week' => 350000, 'label' => '4 tuần',   'save' => 30],
        5  => ['weeks' => 5,  'price_per_week' => 300000, 'label' => '5 tuần',   'save' => 40],
        52 => ['weeks' => 52, 'price_per_week' => 250000, 'label' => '52 tuần (1 năm)', 'save' => 50],
    ];

    public ?int $selectedPlan = null;
    public ?int $selectedCommunityPlanId = null;

    public function selectPlan(int $weeks): void
    {
        $this->selectedPlan = $weeks;
    }

    public function selectCommunityPlan(int $planId): void
    {
        $plan = MembershipPlan::withoutGlobalScopes()
            ->where('brand_id', brand()->id)
            ->whereKey($planId)
            ->where('tier', 'premium')
            ->where('status', 'published')
            ->firstOrFail();

        $this->selectedCommunityPlanId = $plan->id;
        $this->selectedPlan = null;
    }

    public function render()
    {
        $user = Auth::user();
        $membership = $user?->membership;
        $communityPlans = MembershipPlan::withoutGlobalScopes()
            ->where('brand_id', brand()->id)
            ->where('status', 'published')
            ->orderByDesc('tier')->get();
        $selectedCommunityPlan = $this->selectedCommunityPlanId
            ? $communityPlans->firstWhere('id', $this->selectedCommunityPlanId)
            : null;

        // Auto-detect payment success while polling
        if ($this->selectedPlan && $membership && $membership->isActive()) {
            $this->dispatch('toast', message: 'Thanh toán thành công! Membership đã được kích hoạt.', type: 'success');
            $this->selectedPlan = null;
        }

        return view('livewire.membership-pricing', [
            'plans' => self::PLANS,
            'membership' => $membership,
            'communityPlans' => $communityPlans,
            'selectedCommunityPlan' => $selectedCommunityPlan,
        ])->layout('layouts.app', ['title' => 'Gói thành viên — DSCons']);
    }
}
