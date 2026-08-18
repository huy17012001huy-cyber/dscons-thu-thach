<?php
namespace App\Livewire;
use App\Models\CommunityChallenge;
use Livewire\Component;
class SidebarChallenge extends Component {
    public function render() {
        $challenge = CommunityChallenge::whereNull('completed_at')
            ->where('week_end', '>=', now()->toDateString())
            ->latest()->first();
        return view('livewire.sidebar-challenge', ['challenge' => $challenge]);
    }
}
