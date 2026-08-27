<?php

namespace App\Livewire;

use App\Core\Gamification\XpService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class SidebarMyXp extends Component
{
    public function render(): View
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return view('livewire.sidebar-my-xp', ['user' => null, 'progress' => 0, 'toNext' => 0]);
        }

        $xpService = app(XpService::class);

        return view('livewire.sidebar-my-xp', [
            'user' => $user,
            'progress' => $xpService->expProgressPct($user),
            'toNext' => $xpService->expToNextLevel($user),
        ]);
    }
}
