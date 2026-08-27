<?php

declare(strict_types=1);

namespace Modules\Community\Application;

use App\Core\CommunityContext;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Services\XpService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class CommunityModerationService
{
    public function __construct(
        private readonly CommunityContext $context,
        private readonly XpService $xp,
    ) {}

    public function dismissReport(int $reportId, User $actor): bool
    {
        return $this->updateReportStatus($reportId, $actor, 'dismissed');
    }

    public function reviewReport(int $reportId, User $actor): bool
    {
        return $this->updateReportStatus($reportId, $actor, 'reviewed');
    }

    public function deleteReportedContent(int $reportId, User $actor): bool
    {
        $brand = $this->context->require();
        if (! $actor->isCommunityModerator($brand->id)) {
            return false;
        }

        return DB::transaction(function () use ($reportId, $brand): bool {
            $report = $this->lockedReport($reportId, $brand->id);
            $reportable = $report->reportable;
            if ($reportable instanceof Model && $this->belongsToBrand($reportable, $brand->id)) {
                $reportable->delete();
            }
            $report->update(['status' => 'reviewed']);

            return true;
        });
    }

    public function approveCot(int $postId, User $actor): bool
    {
        $brand = $this->context->require();
        if (! $actor->isCommunityAdmin($brand->id)) {
            return false;
        }

        return DB::transaction(function () use ($postId, $brand): bool {
            $post = $this->lockedPost($postId, $brand->id);
            if ($post->is_cot || ! $post->cot_by) {
                return false;
            }

            $post->update(['is_cot' => true, 'cot_at' => now()]);
            $owner = $post->user;
            if ($owner instanceof User) {
                $this->xp->award($owner, 'cot', 1.0, 'Bài viết được chọn CỐT', $post);
                DB::afterCommit(fn () => $owner->notify(new GenericNotification('★', 'Bài viết của bạn đã được duyệt CỐT!')));
            }

            return true;
        });
    }

    public function rejectCot(int $postId, User $actor): bool
    {
        $brand = $this->context->require();
        if (! $actor->isCommunityAdmin($brand->id)) {
            return false;
        }

        return DB::transaction(function () use ($postId, $brand): bool {
            $post = $this->lockedPost($postId, $brand->id);
            if (! $post->cot_by || $post->is_cot) {
                return false;
            }

            $post->update(['cot_by' => null]);

            return true;
        });
    }

    private function updateReportStatus(int $reportId, User $actor, string $status): bool
    {
        $brand = $this->context->require();
        if (! $actor->isCommunityModerator($brand->id)) {
            return false;
        }

        return DB::transaction(function () use ($reportId, $status, $brand): bool {
            $this->lockedReport($reportId, $brand->id)->update(['status' => $status]);

            return true;
        });
    }

    private function lockedReport(int $reportId, int $brandId): Report
    {
        return Report::query()
            ->where('brand_id', $brandId)
            ->whereKey($reportId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function lockedPost(int $postId, int $brandId): Post
    {
        return Post::query()
            ->where('brand_id', $brandId)
            ->whereKey($postId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function belongsToBrand(Model $model, int $brandId): bool
    {
        return $model instanceof Post
            ? $model->brand_id === $brandId
            : $model instanceof Comment && $model->brand_id === $brandId;
    }
}
