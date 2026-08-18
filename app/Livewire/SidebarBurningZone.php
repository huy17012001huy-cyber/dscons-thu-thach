<?php
namespace App\Livewire;
use App\Models\PillarStat;
use Livewire\Component;
class SidebarBurningZone extends Component {
    public function render() {
        $stats = PillarStat::all()->keyBy('pillar');
        $burning = $stats->firstWhere('is_burning', true);
        return view('livewire.sidebar-burning-zone', ['stats' => $stats, 'burning' => $burning]);
    }
}
