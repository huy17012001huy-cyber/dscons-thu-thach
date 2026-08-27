<?php

declare(strict_types=1);

namespace Modules\Community\Application;

use App\Core\CommunityContext;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

final class PostManagementService
{
    private const REPORT_LIMIT = 5;

    public function __construct(private readonly CommunityContext $context) {}

    public function deletePost(Post $post, User $actor): bool
    {
        return DB::transaction(function () use ($post, $actor): bool {
            $post = $this->lockedPost($post);
            if (! $this->canManagePost($post, $actor)) {
                return false;
            }

            $post->delete();

            return true;
        });
    }

    public function updatePost(Post $post, User $actor, string $content): bool
    {
        return DB::transaction(function () use ($post, $actor, $content): bool {
            $post = $this->lockedPost($post);
            if ($post->user_id !== $actor->id) {
                return false;
            }

            $post->update(['content' => $content]);

            return true;
        });
    }

    public function nominateCot(Post $post, User $actor): bool
    {
        return DB::transaction(function () use ($post, $actor): bool {
            $post = $this->lockedPost($post);
            if ($actor->level < 30 || $post->is_cot || $post->cot_by) {
                return false;
            }

            $post->update(['cot_by' => $actor->id]);
            $owner = $post->user;
            if ($owner instanceof User && $owner->id !== $actor->id) {
                DB::afterCommit(fn () => $owner->notify(new GenericNotification(
                    '★',
                    $actor->name.' đề cử bài viết của bạn cho CỐT',
                    null,
                    $post->id,
                )));
            }

            return true;
        });
    }

    public function updateComment(Post $post, int $commentId, User $actor, string $content): bool
    {
        return DB::transaction(function () use ($post, $commentId, $actor, $content): bool {
            $comment = $this->lockedComment($this->lockedPost($post), $commentId);
            if ($comment->user_id !== $actor->id) {
                return false;
            }

            $comment->update(['content' => $content]);

            return true;
        });
    }

    public function deleteComment(Post $post, int $commentId, User $actor): bool
    {
        return DB::transaction(function () use ($post, $commentId, $actor): bool {
            $post = $this->lockedPost($post);
            $comment = $this->lockedComment($post, $commentId);
            if (! $this->canManageComment($comment, $actor, $post->brand_id)) {
                return false;
            }

            $comment->delete();

            return true;
        });
    }

    public function reportPost(Post $post, User $actor): ReportSubmissionOutcome
    {
        return DB::transaction(fn (): ReportSubmissionOutcome => $this->report($this->lockedPost($post), $actor));
    }

    public function reportComment(Post $post, int $commentId, User $actor): ReportSubmissionOutcome
    {
        return DB::transaction(fn (): ReportSubmissionOutcome => $this->report(
            $this->lockedComment($this->lockedPost($post), $commentId),
            $actor,
        ));
    }

    private function report(Post|Comment $reportable, User $actor): ReportSubmissionOutcome
    {
        $brandId = $reportable->brand_id;
        if ($reportable->user_id === $actor->id) {
            return ReportSubmissionOutcome::OwnContent;
        }

        $throttleKey = 'report:'.$actor->id;
        if (RateLimiter::tooManyAttempts($throttleKey, self::REPORT_LIMIT)) {
            return ReportSubmissionOutcome::RateLimited;
        }

        if (Report::query()
            ->where('brand_id', $brandId)
            ->where('user_id', $actor->id)
            ->where('reportable_type', $reportable::class)
            ->where('reportable_id', $reportable->id)
            ->exists()) {
            return ReportSubmissionOutcome::AlreadyReported;
        }

        Report::create([
            'brand_id' => $brandId,
            'user_id' => $actor->id,
            'reportable_type' => $reportable::class,
            'reportable_id' => $reportable->id,
            'reason' => 'Spam / Vi phạm',
        ]);
        RateLimiter::hit($throttleKey, 3600);

        return ReportSubmissionOutcome::Reported;
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

    private function canManagePost(Post $post, User $actor): bool
    {
        return $post->user_id === $actor->id || $actor->isCommunityModerator($post->brand_id);
    }

    private function canManageComment(Comment $comment, User $actor, int $brandId): bool
    {
        return $comment->user_id === $actor->id || $actor->isCommunityModerator($brandId);
    }
}
