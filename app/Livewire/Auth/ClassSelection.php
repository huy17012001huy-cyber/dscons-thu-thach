<?php

namespace App\Livewire\Auth;

use App\Core\Auth\AccountDataService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class ClassSelection extends Component
{
    public string $selectedClass = '';

    /** @var array<string, array<string, mixed>> */
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
        if (! $this->selectedClass || ! array_key_exists($this->selectedClass, $this->classes)) {
            return;
        }

        $user = Auth::user();
        if (! $user instanceof User) {
            return;
        }
        app(AccountDataService::class)->selectClass($user, brand(), $this->selectedClass);

        $this->redirect(route('feed'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.class-selection')
            ->layout('layouts.guest', ['title' => 'Chọn Class — DSCons']);
    }
}
