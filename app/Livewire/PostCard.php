<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Support\PostContentRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;
use Modules\Community\Application\PostInteractionService;

class PostCard extends Component
{
    public Post $post;

    public bool $showComments = false;

    public string $newComment = '';

    public bool $showFull = false;

    public ?int $replyToId = null;

    public ?string $replyToName = null;

    public bool $isLiked = false;

    public bool $isBookmarked = false;

    public int $likesCount = 0;

    public bool $editing = false;

    public string $editContent = '';

    public ?int $editingCommentId = null;

    public string $editCommentContent = '';

    public function mount(Post $post): void
    {
        $this->post = $post;
        $this->likesCount = (int) ($post->likes_count ?? $post->likes()->count());

        if (Auth::check()) {
            $actor = $this->currentUser();
            $this->isLiked = (bool) ($post->likes_exists ?? $post->isLikedBy($actor));
            $this->isBookmarked = (bool) ($post->bookmarks_exists ?? $post->isBookmarkedBy($actor));
        }
    }

    public function toggleLike(): void
    {
        if (! Auth::check()) {
            return;
        }

        $result = app(PostInteractionService::class)->togglePostLike($this->post, $this->currentUser());
        $this->isLiked = $result->isActive;
        $this->likesCount = $result->count;
    }

    public function toggleBookmark(): void
    {
        if (! Auth::check()) {
            return;
        }

        $this->isBookmarked = app(PostInteractionService::class)
            ->toggleBookmark($this->post, $this->currentUser())
            ->isActive;
    }

    public function toggleCommentLike(int $commentId): void
    {
        if (! Auth::check()) {
            return;
        }

        app(PostInteractionService::class)->toggleCommentLike($this->post, $commentId, $this->currentUser());
    }

    public function addComment(): void
    {
        if (! Auth::check() || blank($this->newComment)) {
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

        $this->newComment = '';
        $this->replyToId = null;
        $this->replyToName = null;
        $this->post->refresh();
    }

    public function replyTo(int $commentId, string $name): void
    {
        $this->replyToId = $commentId;
        $this->replyToName = $name;
        $this->showComments = true;
    }

    public function cancelReply(): void
    {
        $this->replyToId = null;
        $this->replyToName = null;
    }

    public function deletePost(): void
    {
        if (! Auth::check()) {
            return;
        }

        $actor = $this->currentUser();
        if ($actor->id !== $this->post->user_id && ! $actor->is_admin) {
            return;
        }

        $this->post->delete();
        $this->dispatch('post-created');
    }

    public function startEdit(): void
    {
        $actor = $this->currentUser();
        if ($actor->id !== $this->post->user_id) {
            return;
        }

        $this->editing = true;
        $this->editContent = $this->post->content;
    }

    public function saveEdit(): void
    {
        $actor = $this->currentUser();
        if ($actor->id !== $this->post->user_id) {
            return;
        }

        $this->validate(['editContent' => 'required|min:5|max:50000']);
        $this->post->update(['content' => $this->editContent]);
        $this->editing = false;
        $this->post->refresh();
    }

    public function cancelEdit(): void
    {
        $this->editing = false;
        $this->editContent = '';
    }

    public function nominateCot(): void
    {
        if (! Auth::check()) {
            return;
        }

        $user = $this->currentUser();
        if ($user->level < 30 || $this->post->is_cot || $this->post->cot_by) {
            return;
        }

        $this->post->update(['cot_by' => $user->id]);
        $owner = $this->post->user;
        if ($owner instanceof User && $owner->id !== $user->id) {
            $owner->notify(new GenericNotification('★', $user->name.' đề cử bài viết của bạn cho CỐT', null, $this->post->id));
        }

        $this->dispatch('toast', message: 'Đã đề cử bài viết cho CỐT!', type: 'success');
    }

    public function reportPost(): void
    {
        if (! Auth::check() || $this->currentUser()->id === $this->post->user_id) {
            return;
        }

        $this->createReport(Post::class, $this->post->id, 'bài viết');
    }

    public function startEditComment(int $commentId): void
    {
        if (! Auth::check()) {
            return;
        }

        $comment = $this->currentComment($commentId);
        if ($this->currentUser()->id !== $comment->user_id) {
            return;
        }

        $this->editingCommentId = $comment->id;
        $this->editCommentContent = $comment->content;
    }

    public function saveEditComment(): void
    {
        if (! Auth::check() || ! $this->editingCommentId) {
            return;
        }

        $comment = $this->currentComment($this->editingCommentId);
        if ($this->currentUser()->id !== $comment->user_id) {
            return;
        }

        $this->validate(['editCommentContent' => 'required|min:1|max:2000']);
        $comment->update(['content' => $this->editCommentContent]);
        $this->cancelEditComment();
        $this->post->refresh();
    }

    public function cancelEditComment(): void
    {
        $this->editingCommentId = null;
        $this->editCommentContent = '';
    }

    public function deleteComment(int $commentId): void
    {
        if (! Auth::check()) {
            return;
        }

        $comment = $this->currentComment($commentId);
        $actor = $this->currentUser();
        if ($actor->id !== $comment->user_id && ! $actor->is_admin) {
            return;
        }

        $comment->delete();
        $this->post->refresh();
    }

    public function reportComment(int $commentId): void
    {
        if (! Auth::check()) {
            return;
        }

        $comment = $this->currentComment($commentId);
        if ($this->currentUser()->id === $comment->user_id) {
            return;
        }

        $this->createReport(Comment::class, $comment->id, 'bình luận');
    }

    public function renderContent(bool $showFull): string
    {
        return app(PostContentRenderer::class)->renderPost($this->post, ! $showFull, 500);
    }

    public function contentPreview(): string
    {
        return Str::limit(trim(strip_tags(app(PostContentRenderer::class)->renderPost($this->post))), 180);
    }

    public function render(): View
    {
        $comments = $this->showComments
            ? $this->post->comments()
                ->with([
                    'user.daKhongCuc',
                    'replies' => fn ($query) => $query->with(['user'])
                        ->withCount('likes')
                        ->withExists(['likes' => fn ($likes) => $likes->where('user_id', Auth::id())]),
                ])
                ->withCount('likes')
                ->withExists(['likes' => fn ($query) => $query->where('user_id', Auth::id())])
                ->oldest()
                ->get()
            : collect();

        return view('livewire.post-card', ['comments' => $comments]);
    }

    private function createReport(string $type, int $id, string $label): void
    {
        $actor = $this->currentUser();
        $throttleKey = 'report:'.$actor->id;
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $this->dispatch('toast', message: 'Bạn báo cáo quá nhiều. Vui lòng thử lại sau '.round(RateLimiter::availableIn($throttleKey) / 60).' phút.', type: 'error');

            return;
        }

        if (Report::query()->where('user_id', $actor->id)->where('reportable_type', $type)->where('reportable_id', $id)->exists()) {
            $this->dispatch('toast', message: 'Bạn đã báo cáo '.$label.' này rồi', type: 'error');

            return;
        }

        Report::create([
            'brand_id' => $this->post->brand_id,
            'user_id' => $actor->id,
            'reportable_type' => $type,
            'reportable_id' => $id,
            'reason' => 'Spam / Vi phạm',
        ]);
        RateLimiter::hit($throttleKey, 3600);
        $this->dispatch('toast', message: 'Đã báo cáo '.$label.' cho Admin', type: 'success');
    }

    private function currentComment(int $commentId): Comment
    {
        return $this->post->allComments()->whereKey($commentId)->firstOrFail();
    }

    private function currentUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        return $user;
    }
}
