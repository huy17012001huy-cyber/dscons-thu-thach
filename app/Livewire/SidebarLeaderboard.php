<?php

namespace App\Livewire;

use App\Models\CommunityUserStat;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Component;

class SidebarLeaderboard extends Component
{
    public function render(): View
    {
        $brandId = app()->bound('brand') ? brand()->id : 0;
        $top = Cache::remember("sidebar_leaderboard:{$brandId}:30d", 300, function () {
            return CommunityUserStat::query()->with('user')
                ->where('updated_at', '>=', now()->subDays(30))
                ->orderByDesc('xp')->take(5)->get();
        });

        return view('livewire.sidebar-leaderboard', ['top' => $top]);
    }
}
