<?php

namespace App\Livewire;

use App\Models\Expedition;
use Illuminate\View\View;
use Livewire\Component;

class SidebarChallenges extends Component
{
    public function render(): View
    {
        $challenges = Expedition::where('status', 'open')->latest()->take(3)->get();

        return view('livewire.sidebar-challenges', ['challenges' => $challenges]);
    }
}
