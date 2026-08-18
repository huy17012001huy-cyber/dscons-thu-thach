<?php
namespace App\Livewire;
use App\Models\Expedition;
use Livewire\Component;
class SidebarChallenges extends Component {
    public function render() {
        $challenges = Expedition::where('status', 'open')->latest()->take(3)->get();
        return view('livewire.sidebar-challenges', ['challenges' => $challenges]);
    }
}
