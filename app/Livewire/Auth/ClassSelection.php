<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ClassSelection extends Component
{
    public string $selectedClass = '';

    public array $classes = [];

    public function mount(): void
    {
        $this->classes = brand()->classProfiles();
    }

    public function selectClass(string $class): void
    {
        $this->selectedClass = $class;
    }

    public function confirm(): void
    {
        if (!$this->selectedClass || !array_key_exists($this->selectedClass, $this->classes)) {
            return;
        }

        $user = Auth::user();
        $user->update(['class' => $this->selectedClass]);

        $this->redirect(route('feed'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.class-selection')
            ->layout('layouts.guest', ['title' => 'Chọn Class — DSCons']);
    }
}
