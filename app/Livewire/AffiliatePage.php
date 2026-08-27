<?php

namespace App\Livewire;

use App\Models\AffiliateEarning;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class AffiliatePage extends Component
{
    public function render(): View
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);
        $referralLink = route('referral', $user->username ?? $user->id);
        $referralCount = $user->referrals()->count();
        $earnings = AffiliateEarning::where('referrer_id', $user->id)
            ->with('referred')
            ->latest()
            ->get();
        $totalEarned = $earnings->where('status', 'paid')->sum('amount');
        $totalPending = $earnings->where('status', 'pending')->sum('amount');

        return view('livewire.affiliate-page', [
            'referralLink' => $referralLink,
            'referralCount' => $referralCount,
            'earnings' => $earnings,
            'totalEarned' => $totalEarned,
            'totalPending' => $totalPending,
        ])->layout('layouts.app', ['title' => 'Affiliate — DSCons']);
    }
}
