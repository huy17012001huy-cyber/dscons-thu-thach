<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SignalsPage extends Component
{
    use WithPagination;

    #[Url]
    public string $pillar = '';

    public function setPillar(string $p): void
    {
        $this->pillar = $p === $this->pillar ? '' : $p;
        $this->resetPage();
    }

    public function render(): View
    {
        $query = Post::with(['user.daKhongCuc', 'images', 'topic'])
            ->withCount(['likes', 'allComments'])
            ->withExists(['likes' => fn ($q) => $q->where('user_id', auth()->id())])
            ->withExists(['bookmarks' => fn ($q) => $q->where('user_id', auth()->id())])
            ->where('is_signal', true)
            ->whereNull('deleted_at');
        if ($this->pillar) {
            $query->where('pillar', $this->pillar);
        }
        $query->latest();
        $todayCount = Post::where('is_signal', true)->whereDate('created_at', today())->count();

        return view('livewire.signals-page', ['posts' => $query->paginate(15), 'todayCount' => $todayCount])
            ->layout('layouts.app', ['title' => 'Tín hiệu công nghệ — DSCons']);
    }
}
