<?php

namespace App\Livewire;

use App\Models\Bookmark;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\Report;
use App\Notifications\GenericNotification;
use App\Services\XpService;
use App\Support\PostContentRenderer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;

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

    public function mount(Post $post): void
    {
        // Use pre-fetched attributes when available to avoid N+1 queries.
        // We ensure relationship counts and existence flags are set.
        $this->post = $post;
        $this->likesCount = (int) ($post->likes_count ?? $post->likes()->count());

        if (Auth::check()) {
            $this->isLiked = (bool) ($post->likes_exists ?? $post->isLikedBy(Auth::user()));
            $this->isBookmarked = (bool) ($post->bookmarks_exists ?? $post->isBookmarkedBy(Auth::user()));
        }
    }

    public function toggleLike(): void
    {
        if (! Auth::check()) {
            return;
        }

        DB::transaction(function () {
            $existing = Like::withTrashed()
                ->where('likeable_type', Post::class)
                ->where('likeable_id', $this->post->id)
                ->where('user_id', Auth::id())
                ->lockForUpdate()
                ->first();

            if ($existing && ! $existing->trashed()) {
                $existing->delete(); // soft delete
                $this->likesCount--;
                $this->isLiked = false;
            } elseif ($existing && $existing->trashed()) {
                $existing->restore(); // unlike rồi like lại → không award XP
                $this->likesCount++;
                $this->isLiked = true;
            } else {
                Like::create([
                    'likeable_type' => Post::class,
                    'likeable_id' => $this->post->id,
                    'user_id' => Auth::id(),
                ]);
                $this->likesCount++;
                $this->isLiked = true;

                // Award EXP to post OWNER + notify (only first like, not self-like)
                $owner = $this->post->user;
                if ($owner->id !== Auth::id()) {
                    app(XpService::class)->award($owner, 'post_liked', 1.0, Auth::user()->name.' thích bài viết', $this->post);
                    $owner->notify(new GenericNotification('♥', Auth::user()->name.' thích bài viết của bạn', null, $this->post->id));
                }
            }
        });
    }

    public function toggleBookmark(): void
    {
        if (! Auth::check()) {
            return;
        }

        DB::transaction(function () {
            $existing = Bookmark::where('user_id', Auth::id())
                ->where('post_id', $this->post->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $existing->delete();
                $this->isBookmarked = false;
            } else {
                $bookmark = Bookmark::firstOrCreate(['user_id' => Auth::id(), 'post_id' => $this->post->id]);
                $this->isBookmarked = true;

                // Award EXP to post owner (only if new bookmark, not duplicate)
                $owner = $this->post->user;
                if ($bookmark->wasRecentlyCreated && $owner->id !== Auth::id()) {
                    app(XpService::class)->award($owner, 'post_bookmarked', 1.0, Auth::user()->name.' lưu bài viết', $this->post);
                }
            }
        });
    }

    public function toggleCommentLike(int $commentId): void
    {
        if (! Auth::check()) {
            return;
        }
        $comment = Comment::findOrFail($commentId);

        DB::transaction(function () use ($comment, $commentId) {
            $existing = Like::withTrashed()
                ->where('likeable_type', Comment::class)
                ->where('likeable_id', $commentId)
                ->where('user_id', Auth::id())
                ->lockForUpdate()
                ->first();

            if ($existing && ! $existing->trashed()) {
                $existing->delete(); // soft delete
            } elseif ($existing && $existing->trashed()) {
                $existing->restore(); // unlike rồi like lại → không award XP
            } else {
                Like::create([
                    'likeable_type' => Comment::class,
                    'likeable_id' => $commentId,
                    'user_id' => Auth::id(),
                ]);
                // Award XP chỉ lần đầu, và không award nếu self-like
                if (Auth::id() !== $comment->user_id) {
                    app(XpService::class)->award($comment->user, 'comment_liked', 1.0, Auth::user()->name.' thích bình luận', $comment);
                }
            }
        });
    }

    public function addComment(): void
    {
        if (! Auth::check() || blank($this->newComment)) {
            return;
        }

        $this->validate(['newComment' => 'required|max:2000']);

        // Anti-spam: max 20 comments per hour
        $recentComments = Comment::where('user_id', Auth::id())
            ->where('created_at', '>=', now()->subHour())->count();
        if ($recentComments >= 20) {
            $this->addError('newComment', 'Bạn đã bình luận quá nhiều. Vui lòng đợi.');

            return;
        }

        $comment = $this->post->allComments()->create([
            'user_id' => Auth::id(),
            'parent_id' => $this->replyToId,
            'content' => $this->newComment,
        ]);

        // Atomic rune claim: only one user can win
        $wonRune = false;
        if ($this->post->isRuneActive()) {
            $affected = Post::where('id', $this->post->id)
                ->whereNull('rune_first_comment_user_id')
                ->update(['rune_first_comment_user_id' => Auth::id()]);
            $wonRune = $affected > 0;
            if ($wonRune) {
                $comment->update(['is_rune_winner' => true]);
            }
        }

        // Commenter gets +1 base EXP (or 2x if rune winner)
        if ($wonRune) {
            app(XpService::class)->award(Auth::user(), 'comment', 2.0, 'Phù văn 2x EXP', $comment);
        } else {
            app(XpService::class)->award(Auth::user(), 'comment', 1.0, null, $comment);
        }

        // Post OWNER gets +3 EXP only for FIRST comment from each unique user
        $owner = $this->post->user;
        if ($owner->id !== Auth::id()) {
            $alreadyCommented = $this->post->allComments()
                ->where('user_id', Auth::id())
                ->where('id', '!=', $comment->id)
                ->exists();

            if (! $alreadyCommented) {
                app(XpService::class)->award($owner, 'post_commented', 1.0, Auth::user()->name.' bình luận bài viết', $this->post);
            }
            $owner->notify(new GenericNotification('💬', Auth::user()->name.' bình luận bài viết của bạn', null, $this->post->id));
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
        if (Auth::id() !== $this->post->user_id && ! Auth::user()->is_admin) {
            return;
        }
        $this->post->delete();
        $this->dispatch('post-created'); // refresh feed
    }

    public bool $editing = false;

    public string $editContent = '';

    public function startEdit(): void
    {
        if (Auth::id() !== $this->post->user_id) {
            return;
        }
        $this->editing = true;
        $this->editContent = $this->post->content;
    }

    public function saveEdit(): void
    {
        if (Auth::id() !== $this->post->user_id) {
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
        $user = Auth::user();
        if ($user->level < 30) {
            return;
        }
        if ($this->post->is_cot || $this->post->cot_by) {
            return;
        }

        $this->post->update(['cot_by' => $user->id]);

        // Notify post owner
        $owner = $this->post->user;
        if ($owner->id !== $user->id) {
            $owner->notify(new GenericNotification('★', $user->name.' đề cử bài viết của bạn cho CỐT', null, $this->post->id));
        }

        $this->dispatch('toast', message: 'Đã đề cử bài viết cho CỐT!', type: 'success');
    }

    public function reportPost(): void
    {
        if (! Auth::check()) {
            return;
        }
        if (Auth::id() === $this->post->user_id) {
            return;
        }

        $throttleKey = 'report:'.Auth::id();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->dispatch('toast', message: 'Bạn báo cáo quá nhiều. Vui lòng thử lại sau '.round($seconds / 60).' phút.', type: 'error');

            return;
        }

        $exists = Report::where('user_id', Auth::id())
            ->where('reportable_type', Post::class)
            ->where('reportable_id', $this->post->id)
            ->exists();

        if ($exists) {
            $this->dispatch('toast', message: 'Bạn đã báo cáo bài viết này rồi', type: 'error');

            return;
        }

        Report::create([
            'user_id' => Auth::id(),
            'reportable_type' => Post::class,
            'reportable_id' => $this->post->id,
            'reason' => 'Spam / Vi phạm',
        ]);

        RateLimiter::hit($throttleKey, 3600);

        $this->dispatch('toast', message: 'Đã báo cáo bài viết cho Admin', type: 'success');
    }

    // ─── Comment actions ──────────────────────────────────────────
    public ?int $editingCommentId = null;

    public string $editCommentContent = '';

    public function startEditComment(int $commentId): void
    {
        if (! Auth::check()) {
            return;
        }
        $comment = Comment::findOrFail($commentId);
        if (Auth::id() !== $comment->user_id) {
            return;
        }
        $this->editingCommentId = $commentId;
        $this->editCommentContent = $comment->content;
    }

    public function saveEditComment(): void
    {
        if (! Auth::check() || ! $this->editingCommentId) {
            return;
        }
        $comment = Comment::findOrFail($this->editingCommentId);
        if (Auth::id() !== $comment->user_id) {
            return;
        }
        $this->validate(['editCommentContent' => 'required|min:1|max:2000']);
        $comment->update(['content' => $this->editCommentContent]);
        $this->editingCommentId = null;
        $this->editCommentContent = '';
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
        $comment = Comment::findOrFail($commentId);
        if (Auth::id() !== $comment->user_id && ! Auth::user()->is_admin) {
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
        $comment = Comment::findOrFail($commentId);
        if (Auth::id() === $comment->user_id) {
            return;
        }

        $throttleKey = 'report:'.Auth::id();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->dispatch('toast', message: 'Bạn báo cáo quá nhiều. Vui lòng thử lại sau '.round($seconds / 60).' phút.', type: 'error');

            return;
        }

        $exists = Report::where('user_id', Auth::id())
            ->where('reportable_type', Comment::class)
            ->where('reportable_id', $commentId)
            ->exists();

        if ($exists) {
            $this->dispatch('toast', message: 'Bạn đã báo cáo bình luận này rồi', type: 'error');

            return;
        }

        Report::create([
            'user_id' => Auth::id(),
            'reportable_type' => Comment::class,
            'reportable_id' => $commentId,
            'reason' => 'Spam / Vi phạm',
        ]);

        RateLimiter::hit($throttleKey, 3600);

        $this->dispatch('toast', message: 'Đã báo cáo bình luận cho Admin', type: 'success');
    }

    public function renderContent(bool $showFull): string
    {
        return app(PostContentRenderer::class)->renderPost($this->post, !$showFull, 500);
    }

    public function contentPreview(): string
    {
        return Str::limit(trim(strip_tags(app(PostContentRenderer::class)->renderPost($this->post))), 180);
    }

    public function render()
    {
        $comments = $this->showComments
            ? $this->post->comments()
                ->with([
                    'user.daKhongCuc',
                    'replies' => fn ($q) => $q->with(['user'])
                        ->withCount('likes')
                        ->withExists(['likes' => fn ($q) => $q->where('user_id', Auth::id())]),
                ])
                ->withCount('likes')
                ->withExists(['likes' => fn ($q) => $q->where('user_id', Auth::id())])
                ->oldest()
                ->get()
            : collect();

        return view('livewire.post-card', [
            'comments' => $comments,
        ]);
    }
}
