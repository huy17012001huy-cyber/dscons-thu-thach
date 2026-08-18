<?php
namespace App\Livewire;
use App\Models\Expedition;
use Livewire\Component;
use Livewire\WithPagination;

class ChallengePage extends Component {
    use WithPagination;

    public function render() {
        $challenges = Expedition::with('leader')
            ->whereIn('status', ['active', 'open'])
            ->latest()
            ->paginate(12);

        return view('livewire.challenge-page', ['challenges' => $challenges])
            ->layout('layouts.app', ['title' => 'Challenge — The All In Plan™']);
    }
}
