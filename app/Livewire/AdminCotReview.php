<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Modules\Community\Application\CommunityModerationService;

class AdminCotReview extends Component
{
    public function approve(int $postId): void
    {
        $this->authorizeAdmin();
        app(CommunityModerationService::class)->approveCot($postId, $this->currentUser());
    }

    public function reject(int $postId): void
    {
        $this->authorizeAdmin();
        app(CommunityModerationService::class)->rejectCot($postId, $this->currentUser());
    }

    public function render(): View
    {
        $this->authorizeAdmin();
        $pending = Post::query()
            ->whereNotNull('cot_by')
            ->where('is_cot', false)
            ->with(['user', 'cotBy'])
            ->latest()
            ->get();

        return view('livewire.admin-cot-review', ['pending' => $pending])
            ->layout('layouts.app', ['title' => 'Duyệt CỐT — Admin']);
    }

    private function authorizeAdmin(): void
    {
        abort_unless($this->currentUser()->isCommunityAdmin(), 403);
    }

    private function currentUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        return $user;
    }
}
