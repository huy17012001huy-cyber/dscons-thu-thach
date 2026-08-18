<?php

namespace App\Livewire;

use App\Models\Post;
use App\Notifications\GenericNotification;
use App\Services\XpService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AdminCotReview extends Component
{
    public function approve(int $postId): void
    {
        if (!Auth::user()?->is_admin) return;
        $post = Post::with('user')->findOrFail($postId);
        $post->update(['is_cot' => true, 'cot_at' => now()]);

        // Award XP to author
        app(XpService::class)->award($post->user, 'cot', 1.0, 'Bài viết được chọn CỐT', $post);

        // Notify author
        $post->user->notify(new GenericNotification('★', 'Bài viết của bạn đã được duyệt CỐT!'));
    }

    public function reject(int $postId): void
    {
        if (!Auth::user()?->is_admin) return;
        $post = Post::findOrFail($postId);
        $post->update(['cot_by' => null]);
    }

    public function render()
    {
        $pending = Post::whereNotNull('cot_by')
            ->where('is_cot', false)
            ->with(['user', 'cotBy'])
            ->latest()
            ->get();

        return view('livewire.admin-cot-review', ['pending' => $pending])
            ->layout('layouts.app', ['title' => 'Duyệt CỐT — Admin']);
    }
}
