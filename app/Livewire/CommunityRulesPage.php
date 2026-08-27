<?php

namespace App\Livewire;

use App\Models\User;
use App\Support\CommunityContentDefaults;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CommunityRulesPage extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->check(), 403);
        $user = auth()->user();
        abort_unless($user instanceof User && $user->isCommunityParticipant(brand()->id), 403);
    }

    public function render(): View
    {
        $content = CommunityContentDefaults::resolve(brand()->rules_content, CommunityContentDefaults::rules());

        return view('livewire.community-rules-page', [
            'sections' => CommunityContentDefaults::sections($content),
        ])->layout('layouts.app', ['title' => 'Nội quy · '.brand()->name]);
    }
}
