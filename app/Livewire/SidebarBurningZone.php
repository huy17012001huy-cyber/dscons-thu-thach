<?php

namespace App\Livewire;

use App\Models\PillarStat;
use Illuminate\View\View;
use Livewire\Component;

class SidebarBurningZone extends Component
{
    public function render(): View
    {
        $stats = PillarStat::all()->keyBy('pillar');
        $burning = $stats->firstWhere('is_burning', true);

        return view('livewire.sidebar-burning-zone', ['stats' => $stats, 'burning' => $burning]);
    }
}
