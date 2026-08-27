<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\User;
use App\Support\PostContentRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\Community\Application\PostInteractionService;

class PostPage extends Component
{
    public ?Post $post = null;

    public string $newComment = '';

    public ?int $replyToId = null;

    public ?string $replyToName = null;

    /** @var array<int, array{username:string,name:string,avatar_url:string}> */
    public array $mentionResults = [];

    public bool $isLiked = false;

    public bool $isBookmarked = false;

    public int $likesCount = 0;

    public function mount(string $slug): void
    {
        $this->loadPost($slug);
    }

    public function replyTo(int $commentId, string $name): void
    {
        $this->replyToId = $commentId;
        $this->replyToName = $name;
    }

    public function cancelReply(): void
    {
        $this->replyToId = null;
        $this->replyToName = null;
    }

    public function addComment(): void
    {
        if (! Auth::check() || ! $this->post || blank($this->newComment)) {
            return;
        }

        $this->validate(['newComment' => 'required|max:2000']);
        $result = app(PostInteractionService::class)->addComment(
            $this->post,
            $this->currentUser(),
            $this->newComment,
            $this->replyToId,
        );
        if ($result->isRateLimited) {
            $this->addError('newComment', 'Bạn đã bình luận quá nhiều. Vui lòng đợi.');

            return;
        }

        $slug = $this->post->slug;
        if (is_string($slug)) {
            $this->loadPost($slug);
        }
    }

    public function toggleLike(): void
    {
        if (! Auth::check() || ! $this->post) {
            return;
        }

        $result = app(PostInteractionService::class)->togglePostLike($this->post, $this->currentUser());
        $this->isLiked = $result->isActive;
        $this->likesCount = $result->count;
    }

    public function toggleBookmark(): void
    {
        if (! Auth::check() || ! $this->post) {
            return;
        }

        $this->isBookmarked = app(PostInteractionService::class)
            ->toggleBookmark($this->post, $this->currentUser())
            ->isActive;
    }

    public function renderedPostContent(): string
    {
        return $this->post ? app(PostContentRenderer::class)->renderPost($this->post) : '';
    }

    public function render(): View
    {
        return view('livewire.post-page')
            ->layout('layouts.app', ['title' => ($this->post?->title ?: 'Bài viết').' — '.brand()->name]);
    }

    private function loadPost(string $slug): void
    {
        $this->post = Post::with([
            'user.daKhongCuc', 'topic', 'subject', 'postType', 'images',
            'allComments' => fn ($query) => $query
                ->with(['user.daKhongCuc', 'replies.user.daKhongCuc'])
                ->oldest(),
        ])
            ->withCount(['likes', 'allComments'])
            ->withExists(['likes' => fn ($query) => $query->where('user_id', Auth::id())])
            ->withExists(['bookmarks' => fn ($query) => $query->where('user_id', Auth::id())])
            ->where('brand_id', brand()->id)
            ->where('slug', $slug)
            ->first();

        abort_unless($this->post instanceof Post, 404);
        $this->likesCount = (int) $this->post->likes_count;
        $this->isLiked = (bool) $this->post->likes_exists;
        $this->isBookmarked = (bool) $this->post->bookmarks_exists;
        $this->resetComposer();
    }

    private function resetComposer(): void
    {
        $this->newComment = '';
        $this->replyToId = null;
        $this->replyToName = null;
        $this->mentionResults = [];
    }

    private function currentUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        return $user;
    }
}
