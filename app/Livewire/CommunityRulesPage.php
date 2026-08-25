<?php

namespace App\Livewire;

use App\Support\CommunityContentDefaults;
use Livewire\Component;

class CommunityRulesPage extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->check(), 403);
        abort_unless(auth()->user()->isCommunityParticipant(brand()->id), 403);
    }

    public function render()
    {
        $content = CommunityContentDefaults::resolve(brand()->rules_content, CommunityContentDefaults::rules());

        return view('livewire.community-rules-page', [
            'sections' => CommunityContentDefaults::sections($content),
        ])->layout('layouts.app', ['title' => 'Nội quy · '.brand()->name]);
    }
}
