<?php

declare(strict_types=1);

namespace Modules\Community\Application;

use App\Core\CommunityContext;
use App\Models\Bookmark;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Services\XpService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class PostInteractionService
{
    private const MAX_COMMENTS_PER_HOUR = 20;

    public function __construct(
        private readonly CommunityContext $context,
        private readonly XpService $xp,
    ) {}

    public function togglePostLike(Post $post, User $actor): PostReactionResult
    {
        return DB::transaction(function () use ($post, $actor): PostReactionResult {
            $post = $this->lockedPost($post);
            $like = Like::withTrashed()
                ->where('brand_id', $post->brand_id)
                ->where('likeable_type', Post::class)
                ->where('likeable_id', $post->id)
                ->where('user_id', $actor->id)
                ->lockForUpdate()
                ->first();

            if ($like?->trashed() === false) {
                $like->delete();

                return new PostReactionResult(false, $this->activePostLikeCount($post));
            }
            if ($like) {
                $like->restore();

                return new PostReactionResult(true, $this->activePostLikeCount($post));
            }

            Like::create([
                'brand_id' => $post->brand_id,
                'likeable_type' => Post::class,
                'likeable_id' => $post->id,
                'user_id' => $actor->id,
            ]);
            $this->awardPostLike($post, $actor);

            return new PostReactionResult(true, $this->activePostLikeCount($post));
        });
    }

    public function toggleBookmark(Post $post, User $actor): PostReactionResult
    {
        return DB::transaction(function () use ($post, $actor): PostReactionResult {
            $post = $this->lockedPost($post);
            $bookmark = Bookmark::query()
                ->where('brand_id', $post->brand_id)
                ->where('user_id', $actor->id)
                ->where('post_id', $post->id)
                ->lockForUpdate()
                ->first();

            if ($bookmark) {
                $bookmark->delete();

                return new PostReactionResult(false);
            }

            Bookmark::create([
                'brand_id' => $post->brand_id,
                'user_id' => $actor->id,
                'post_id' => $post->id,
            ]);
            $this->awardBookmark($post, $actor);

            return new PostReactionResult(true);
        });
    }

    public function toggleCommentLike(Post $post, int $commentId, User $actor): PostReactionResult
    {
        return DB::transaction(function () use ($post, $commentId, $actor): PostReactionResult {
            $post = $this->lockedPost($post);
            $comment = $this->lockedComment($post, $commentId);
            $like = Like::withTrashed()
                ->where('brand_id', $post->brand_id)
                ->where('likeable_type', Comment::class)
                ->where('likeable_id', $comment->id)
                ->where('user_id', $actor->id)
                ->lockForUpdate()
                ->first();

            if ($like?->trashed() === false) {
                $like->delete();

                return new PostReactionResult(false);
            }
            if ($like) {
                $like->restore();

                return new PostReactionResult(true);
            }

            Like::create([
                'brand_id' => $post->brand_id,
                'likeable_type' => Comment::class,
                'likeable_id' => $comment->id,
                'user_id' => $actor->id,
            ]);
            $this->awardCommentLike($comment, $actor);

            return new PostReactionResult(true);
        });
    }

    public function addComment(Post $post, User $actor, string $content, ?int $parentId = null): CommentSubmissionResult
    {
        return DB::transaction(function () use ($post, $actor, $content, $parentId): CommentSubmissionResult {
            $post = $this->lockedPost($post);
            if ($this->hasReachedCommentLimit($actor, $post->brand_id)) {
                return CommentSubmissionResult::rateLimited();
            }

            $comment = Comment::create([
                'brand_id' => $post->brand_id,
                'post_id' => $post->id,
                'user_id' => $actor->id,
                'parent_id' => $parentId === null ? null : $this->lockedComment($post, $parentId)->id,
                'content' => $content,
            ]);
            $this->awardComment($post, $comment, $actor);

            return CommentSubmissionResult::submitted($comment);
        });
    }

    private function lockedPost(Post $post): Post
    {
        $brand = $this->context->require();
        if ($post->brand_id !== $brand->id) {
            throw new AuthorizationException('Post does not belong to the current community.');
        }

        return Post::query()
            ->where('brand_id', $brand->id)
            ->whereKey($post->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function lockedComment(Post $post, int $commentId): Comment
    {
        return Comment::query()
            ->where('brand_id', $post->brand_id)
            ->where('post_id', $post->id)
            ->whereKey($commentId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function activePostLikeCount(Post $post): int
    {
        return Like::query()
            ->where('brand_id', $post->brand_id)
            ->where('likeable_type', Post::class)
            ->where('likeable_id', $post->id)
            ->count();
    }

    private function hasReachedCommentLimit(User $actor, int $brandId): bool
    {
        return Comment::query()
            ->where('brand_id', $brandId)
            ->where('user_id', $actor->id)
            ->where('created_at', '>=', now()->subHour())
            ->count() >= self::MAX_COMMENTS_PER_HOUR;
    }

    private function awardPostLike(Post $post, User $actor): void
    {
        $owner = $post->user;
        if (! $owner instanceof User || $owner->id === $actor->id) {
            return;
        }

        $this->xp->award($owner, 'post_liked', 1.0, $actor->name.' thích bài viết', $post);
        DB::afterCommit(fn () => $owner->notify(new GenericNotification(
            '♥',
            $actor->name.' thích bài viết của bạn',
            null,
            $post->id,
        )));
    }

    private function awardBookmark(Post $post, User $actor): void
    {
        $owner = $post->user;
        if ($owner instanceof User && $owner->id !== $actor->id) {
            $this->xp->award($owner, 'post_bookmarked', 1.0, $actor->name.' lưu bài viết', $post);
        }
    }

    private function awardCommentLike(Comment $comment, User $actor): void
    {
        $owner = $comment->user;
        if ($owner instanceof User && $owner->id !== $actor->id) {
            $this->xp->award($owner, 'comment_liked', 1.0, $actor->name.' thích bình luận', $comment);
        }
    }

    private function awardComment(Post $post, Comment $comment, User $actor): void
    {
        $wonRune = $this->claimRune($post, $comment, $actor);
        $this->xp->award($actor, 'comment', $wonRune ? 2.0 : 1.0, $wonRune ? 'Phù văn 2x EXP' : null, $comment);

        $owner = $post->user;
        if (! $owner instanceof User || $owner->id === $actor->id) {
            return;
        }

        $hasPreviousComment = Comment::query()
            ->where('brand_id', $post->brand_id)
            ->where('post_id', $post->id)
            ->where('user_id', $actor->id)
            ->whereKeyNot($comment->id)
            ->exists();
        if (! $hasPreviousComment) {
            $this->xp->award($owner, 'post_commented', 1.0, $actor->name.' bình luận bài viết', $post);
        }

        DB::afterCommit(fn () => $this->notifyCommentRecipients($post, $comment, $actor, $owner));
    }

    private function claimRune(Post $post, Comment $comment, User $actor): bool
    {
        if (! $post->isRuneActive()) {
            return false;
        }

        $claimed = Post::query()
            ->where('brand_id', $post->brand_id)
            ->whereKey($post->id)
            ->whereNull('rune_first_comment_user_id')
            ->update(['rune_first_comment_user_id' => $actor->id]);
        if ($claimed === 0) {
            return false;
        }

        $comment->update(['is_rune_winner' => true]);

        return true;
    }

    private function notifyCommentRecipients(Post $post, Comment $comment, User $actor, User $owner): void
    {
        $owner->notify(new GenericNotification('💬', $actor->name.' bình luận bài viết của bạn', null, $post->id));

        preg_match_all('/@([a-zA-Z0-9._-]+)/', $comment->content, $matches);
        $usernames = array_unique($matches[1]);
        if ($usernames === []) {
            return;
        }

        User::query()
            ->whereIn('username', $usernames)
            ->whereNotIn('id', [$actor->id, $owner->id])
            ->each(fn (User $user) => $user->notify(new GenericNotification(
                '@',
                $actor->name.' đã nhắc đến bạn trong một bình luận',
                null,
                $post->id,
            )));
    }
}
