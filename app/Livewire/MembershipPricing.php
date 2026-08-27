<?php

namespace App\Livewire;

use App\Models\MembershipPlan;
use App\Support\CommunityBrandSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class MembershipPricing extends Component
{
    /**
     * Kept for historical payment references while older communities move to
     * their own MembershipPlan records.
     *
     * @var array<int, array{weeks: int, price_per_week: int, label: string, save: int}>
     */
    public const PLANS = [
        1 => ['weeks' => 1, 'price_per_week' => 500000, 'label' => '1 tuần', 'save' => 0],
        4 => ['weeks' => 4, 'price_per_week' => 350000, 'label' => '4 tuần', 'save' => 30],
        5 => ['weeks' => 5, 'price_per_week' => 300000, 'label' => '5 tuần', 'save' => 40],
        52 => ['weeks' => 52, 'price_per_week' => 250000, 'label' => '52 tuần (1 năm)', 'save' => 50],
    ];

    public ?int $selectedPlan = null;

    public ?int $selectedCommunityPlanId = null;

    public function selectPlan(int $weeks): void
    {
        $this->selectedPlan = $weeks;
        $this->selectedCommunityPlanId = null;
    }

    public function selectCommunityPlan(int $planId): void
    {
        $plan = MembershipPlan::withoutGlobalScopes()
            ->where('brand_id', brand()->id)
            ->whereKey($planId)
            ->where('tier', 'premium')
            ->where('status', 'published')
            ->where('price', '>', 0)
            ->firstOrFail();

        $this->selectedCommunityPlanId = $plan->id;
        $this->selectedPlan = null;
    }

    public function render(): View
    {
        $brand = brand();
        $user = Auth::user();
        $membership = $user?->memberships()
            ->withoutGlobalScopes()
            ->where('brand_id', $brand->id)
            ->latest()
            ->first();

        $communityPlans = MembershipPlan::withoutGlobalScopes()
            ->where('brand_id', $brand->id)
            ->where('status', 'published')
            ->orderByDesc('tier')
            ->orderBy('price')
            ->get();
        $premiumPlans = $communityPlans
            ->filter(fn (MembershipPlan $plan): bool => $plan->isPremium() && $plan->price > 0)
            ->values();
        $selectedCommunityPlan = $this->selectedCommunityPlanId
            ? $premiumPlans->firstWhere('id', $this->selectedCommunityPlanId)
            : null;

        if ($this->selectedCommunityPlanId && ! $selectedCommunityPlan) {
            $this->selectedCommunityPlanId = null;
        }

        if (($this->selectedPlan || $this->selectedCommunityPlanId)
            && $membership?->isPremium()
            && $membership->isActive()) {
            $this->dispatch('toast', message: 'Thanh toán thành công! Membership đã được kích hoạt.', type: 'success');
            $this->selectedPlan = null;
            $this->selectedCommunityPlanId = null;
        }

        return view('livewire.membership-pricing', [
            'plans' => self::PLANS,
            'membership' => $membership,
            'membershipLabel' => CommunityBrandSettings::membershipLabel($brand),
            'communityPlans' => $communityPlans,
            'premiumPlans' => $premiumPlans,
            'selectedCommunityPlan' => $selectedCommunityPlan,
        ])->layout('layouts.app', ['title' => 'Gói thành viên — '.$brand->name]);
    }
}
