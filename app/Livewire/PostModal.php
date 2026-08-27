<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\User;
use App\Support\PostContentRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Community\Application\PostInteractionService;

class PostModal extends Component
{
    public ?Post $post = null;

    public bool $show = false;

    public string $newComment = '';

    public ?int $replyToId = null;

    public ?string $replyToName = null;

    /** @var array<int, array{username:string,name:string,avatar_url:string}> */
    public array $mentionResults = [];

    public bool $isLiked = false;

    public bool $isBookmarked = false;

    public int $likesCount = 0;

    #[On('open-post')]
    public function openPost(int $postId): void
    {
        $this->post = Post::with(['user.daKhongCuc', 'topic', 'subject', 'postType', 'images'])
            ->withCount(['likes', 'allComments'])
            ->withExists(['likes' => fn ($query) => $query->where('user_id', Auth::id())])
            ->withExists(['bookmarks' => fn ($query) => $query->where('user_id', Auth::id())])
            ->with([
                'allComments' => fn ($query) => $query->with(['user.daKhongCuc', 'replies.user.daKhongCuc'])
                    ->withCount('likes')
                    ->withExists(['likes' => fn ($likes) => $likes->where('user_id', Auth::id())])
                    ->oldest(),
            ])
            ->where('brand_id', brand()->id)
            ->find($postId);

        $this->show = $this->post instanceof Post;
        if (! $this->post) {
            $this->likesCount = 0;
            $this->isLiked = false;
            $this->isBookmarked = false;
            $this->resetComposer();

            return;
        }

        $this->likesCount = (int) $this->post->likes_count;
        $this->isLiked = (bool) $this->post->likes_exists;
        $this->isBookmarked = (bool) $this->post->bookmarks_exists;
        $this->resetComposer();
    }

    public function close(): void
    {
        $this->show = false;
        $this->post = null;
        $this->isLiked = false;
        $this->isBookmarked = false;
        $this->likesCount = 0;
        $this->resetComposer();
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

    public function renderedPostContent(): string
    {
        return $this->post ? app(PostContentRenderer::class)->renderPost($this->post) : '';
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

    public function searchMentions(string $query): void
    {
        $query = trim($query);
        if ($query === '') {
            $this->mentionResults = [];

            return;
        }

        $this->mentionResults = User::query()
            ->where(fn ($users) => $users
                ->where('username', 'ilike', $query.'%')
                ->orWhere('name', 'ilike', '%'.$query.'%'))
            ->whereKeyNot(Auth::id())
            ->orderByRaw('CASE WHEN username ILIKE ? THEN 0 ELSE 1 END', [$query.'%'])
            ->limit(5)
            ->get(['id', 'username', 'name', 'avatar'])
            ->map(fn (User $user) => [
                'username' => $user->username,
                'name' => $user->name,
                'avatar_url' => $user->avatar_url,
            ])
            ->toArray();
    }

    public function clearMentions(): void
    {
        $this->mentionResults = [];
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

        $postId = $this->post->id;
        $this->resetComposer();
        $this->openPost($postId);
    }

    public function render(): View
    {
        return view('livewire.post-modal');
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
