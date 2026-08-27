<?php

namespace App\Livewire;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CommunityFeedbacks extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->check(), 403);
        $user = auth()->user();
        abort_unless($user instanceof User && $user->isCommunityParticipant(brand()->id), 403);
    }

    public function render(): View
    {
        $query = Feedback::query()
            ->where('brand_id', brand()->id)
            ->with('user')
            ->latest();

        // Members only need to see their own requests. A community admin can
        // use this page as a lightweight inbox as well as the full admin view.
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        if (! $user->isBrandAdmin(brand()->id)) {
            $query->where('user_id', auth()->id());
        }

        return view('livewire.community-feedbacks', [
            'feedbacks' => $query->get(),
            'isAdmin' => $user->isBrandAdmin(brand()->id),
        ])->layout('layouts.app', ['title' => 'Góp ý & Khiếu nại · '.brand()->name]);
    }
}
