<?php

namespace App\Livewire;

use App\Models\CommunityPostType;
use App\Models\CommunitySubject;
use App\Models\PillarStat;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Feed extends Component
{
    use WithPagination;

    #[Url]
    public string $tab = 'latest';

    #[Url]
    public string $pillar = '';

    #[Url]
    public ?int $subject_id = null;

    #[Url]
    public ?int $post_type_id = null;

    #[Url]
    public ?int $post = null;

    protected string $paginationTheme = 'tailwind';

    public function mount(): void
    {
        if ($this->post) {
            $post = Post::query()->find($this->post);
            if ($post?->slug) {
                $this->redirect(community_route('post.show', ['slug' => $post->slug]), navigate: true);
            }
        }
    }

    #[On('post-created')]
    public function refreshFeed(): void
    {
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->pillar = '';
        $this->subject_id = null;
        $this->post_type_id = null;
        $this->resetPage();
    }

    public function setPillar(string $pillar): void
    {
        $this->tab = 'pillar';
        $this->pillar = $pillar;
        $this->resetPage();
    }

    public function setSubject(int|string|null $subjectId): void
    {
        $this->subject_id = filled($subjectId) ? (int) $subjectId : null;
        $this->tab = 'latest';
        $this->resetPage();
    }

    public function setPostType(int|string|null $postTypeId): void
    {
        $this->post_type_id = filled($postTypeId) ? (int) $postTypeId : null;
        $this->tab = 'latest';
        $this->resetPage();
    }

    public function render(): View
    {
        $brandId = app()->bound('brand') ? (int) brand()->id : 0;
        $query = Post::with(['user.daKhongCuc', 'images', 'topic', 'subject', 'postType'])
            ->withCount(['likes', 'allComments'])
            ->withExists(['likes' => fn ($q) => $q->where('user_id', Auth::id())])
            ->withExists(['bookmarks' => fn ($q) => $q->where('user_id', Auth::id())])
            ->whereNull('deleted_at');

        match ($this->tab) {
            'cot' => $query->where('is_cot', true),
            'pillar' => $query->where('pillar', $this->pillar),
            'popular' => $query->where('created_at', '>=', now()->subDays(7))
                ->orderByDesc(
                    Post::selectRaw('count(*)')
                        ->from('likes')
                        ->whereColumn('likes.likeable_id', 'posts.id')
                        ->where('likes.likeable_type', Post::class)
                ),
            default => null,
        };

        if ($this->subject_id) {
            $query->where('subject_id', $this->subject_id);
        }
        if ($this->post_type_id) {
            $query->where('post_type_id', $this->post_type_id);
        }

        if ($this->tab !== 'popular') {
            $query->latest();
        }

        // Pinned posts always on top — cache base query (60s, hiếm thay đổi),
        // user-specific liked/bookmarked state add fresh per request.
        $pinnedBase = Cache::remember("feed.pinned_posts.base:{$brandId}", 60, function () {
            return Post::with(['user.daKhongCuc', 'images', 'topic', 'subject', 'postType'])
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

        $activeRune = Cache::remember("feed.active_rune:{$brandId}", 30, function () {
            return Post::with('user')
                ->where('rune_active', true)
                ->where('rune_expires_at', '>', now())
                ->whereNull('rune_first_comment_user_id')
                ->first();
        });

        return view('livewire.feed', [
            'posts' => $posts,
            'pinnedPosts' => $pinned,
            'burningPillar' => $burningPillar,
            'activeRune' => $activeRune,
            'subjects' => CommunitySubject::active()
                ->where('slug', '!=', 'tieu-chuan')
                ->get(),
            'postTypes' => CommunityPostType::active()->get(),
        ])->layout('layouts.app', ['title' => 'Bảng tin']);
    }
}
