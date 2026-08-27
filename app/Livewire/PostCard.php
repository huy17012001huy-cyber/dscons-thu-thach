<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Support\PostContentRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Modules\Community\Application\PostInteractionService;
use Modules\Community\Application\PostManagementService;
use Modules\Community\Application\ReportSubmissionOutcome;

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

        if (app(PostManagementService::class)->deletePost($this->post, $this->currentUser())) {
            $this->dispatch('post-created');
        }
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
        $this->validate(['editContent' => 'required|min:5|max:50000']);
        if (app(PostManagementService::class)->updatePost($this->post, $this->currentUser(), $this->editContent)) {
            $this->editing = false;
            $this->post->refresh();
        }
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

        if (app(PostManagementService::class)->nominateCot($this->post, $this->currentUser())) {
            $this->dispatch('toast', message: 'Đã đề cử bài viết cho CỐT!', type: 'success');
        }
    }

    public function reportPost(): void
    {
        if (! Auth::check()) {
            return;
        }

        $this->showReportOutcome(app(PostManagementService::class)->reportPost($this->post, $this->currentUser()), 'bài viết');
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

        $this->validate(['editCommentContent' => 'required|min:1|max:2000']);
        if (app(PostManagementService::class)->updateComment(
            $this->post,
            $this->editingCommentId,
            $this->currentUser(),
            $this->editCommentContent,
        )) {
            $this->cancelEditComment();
            $this->post->refresh();
        }
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

        if (app(PostManagementService::class)->deleteComment($this->post, $commentId, $this->currentUser())) {
            $this->post->refresh();
        }
    }

    public function reportComment(int $commentId): void
    {
        if (! Auth::check()) {
            return;
        }

        $this->showReportOutcome(
            app(PostManagementService::class)->reportComment($this->post, $commentId, $this->currentUser()),
            'bình luận',
        );
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

    private function showReportOutcome(ReportSubmissionOutcome $outcome, string $label): void
    {
        match ($outcome) {
            ReportSubmissionOutcome::Reported => $this->dispatch('toast', message: 'Đã báo cáo '.$label.' cho Admin', type: 'success'),
            ReportSubmissionOutcome::AlreadyReported => $this->dispatch('toast', message: 'Bạn đã báo cáo '.$label.' này rồi', type: 'error'),
            ReportSubmissionOutcome::RateLimited => $this->dispatch('toast', message: 'Bạn báo cáo quá nhiều. Vui lòng thử lại sau.', type: 'error'),
            ReportSubmissionOutcome::OwnContent => null,
        };
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
