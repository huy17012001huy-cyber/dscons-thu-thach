<?php

namespace App\Livewire;

use App\Models\PillarStat;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Feed extends Component
{
    use WithPagination;

    #[Url]
    public string $tab = 'latest';   // latest|cot|popular|offer|traffic|conversion|delivery|continuity|signal

    #[Url]
    public string $pillar = '';

    #[Url]
    public ?int $post = null;

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        if ($this->post) {
            $this->dispatch('open-post', postId: $this->post);
            $this->post = null;
        }
    }

    #[On('post-created')]
    public function refreshFeed(): void
    {
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        $this->tab   = $tab;
        $this->pillar = '';
        $this->resetPage();
    }

    public function setPillar(string $pillar): void
    {
        $this->tab   = 'pillar';
        $this->pillar = $pillar;
        $this->resetPage();
    }

    public function render()
    {
        $query = Post::with(['user.daKhongCuc', 'images', 'topic'])
            ->withCount(['likes', 'allComments'])
            ->withExists(['likes' => fn($q) => $q->where('user_id', Auth::id())])
            ->withExists(['bookmarks' => fn($q) => $q->where('user_id', Auth::id())])
            ->whereNull('deleted_at');

        match($this->tab) {
            'cot'     => $query->where('is_cot', true),
            'signal'  => $query->where('is_signal', true),
            'pillar'  => $query->where('pillar', $this->pillar),
            'popular' => $query->where('created_at', '>=', now()->subDays(7))
                               ->orderByDesc(
                                   Post::selectRaw('count(*)')
                                       ->from('likes')
                                       ->whereColumn('likes.likeable_id', 'posts.id')
                                       ->where('likes.likeable_type', Post::class)
                               ),
            default   => null,
        };

        if ($this->tab !== 'popular') {
            $query->latest();
        }

        // Pinned posts always on top — cache base query (60s, hiếm thay đổi),
        // user-specific liked/bookmarked state add fresh per request.
        $pinnedBase = Cache::remember('feed.pinned_posts.base', 60, function () {
            return Post::with(['user.daKhongCuc', 'images', 'topic'])
                ->withCount(['likes', 'allComments'])
                ->where('is_pinned', true)
                ->whereNull('deleted_at')
                ->latest()
                ->get();
        });
        $pinnedIds = $pinnedBase->pluck('id');
        $likedIds = $pinnedIds->isEmpty() ? collect() : \DB::table('likes')
            ->where('likeable_type', Post::class)
            ->whereIn('likeable_id', $pinnedIds)
            ->where('user_id', Auth::id())
            ->pluck('likeable_id');
        $bookmarkedIds = $pinnedIds->isEmpty() ? collect() : \DB::table('bookmarks')
            ->whereIn('post_id', $pinnedIds)
            ->where('user_id', Auth::id())
            ->pluck('post_id');
        $pinned = $pinnedBase->each(function ($p) use ($likedIds, $bookmarkedIds) {
            $p->setAttribute('likes_exists', $likedIds->contains($p->id));
            $p->setAttribute('bookmarks_exists', $bookmarkedIds->contains($p->id));
        });

        $posts = $query->where('is_pinned', false)->paginate(15);

        $burningPillar = PillarStat::where('is_burning', true)->first();

        $activeRune = Cache::remember('feed.active_rune', 30, function () {
            return Post::with('user')
                ->where('rune_active', true)
                ->where('rune_expires_at', '>', now())
                ->whereNull('rune_first_comment_user_id')
                ->first();
        });

        return view('livewire.feed', [
            'posts'         => $posts,
            'pinnedPosts'   => $pinned,
            'burningPillar' => $burningPillar,
            'activeRune'    => $activeRune,
        ])->layout('layouts.app', ['title' => 'Bảng tin — DSCons AI-BIM Lab']);
    }
}
