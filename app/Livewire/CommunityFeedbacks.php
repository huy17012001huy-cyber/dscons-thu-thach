<?php

namespace App\Livewire;

use App\Models\Feedback;
use Livewire\Component;

class CommunityFeedbacks extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->check(), 403);
        abort_unless(auth()->user()->isCommunityParticipant(brand()->id), 403);
    }

    public function render()
    {
        $query = Feedback::query()
            ->where('brand_id', brand()->id)
            ->with('user')
            ->latest();

        // Members only need to see their own requests. A community admin can
        // use this page as a lightweight inbox as well as the full admin view.
        if (! auth()->user()->isBrandAdmin(brand()->id)) {
            $query->where('user_id', auth()->id());
        }

        return view('livewire.community-feedbacks', [
            'feedbacks' => $query->get(),
            'isAdmin' => auth()->user()->isBrandAdmin(brand()->id),
        ])->layout('layouts.app', ['title' => 'Góp ý & Khiếu nại · '.brand()->name]);
    }
}
