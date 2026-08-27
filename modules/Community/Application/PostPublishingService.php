<?php

declare(strict_types=1);

namespace Modules\Community\Application;

use App\Core\CommunityContext;
use App\Models\CommunityPostType;
use App\Models\CommunitySubject;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\Topic;
use App\Models\User;
use App\Support\PostHtmlSanitizer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class PostPublishingService
{
    public function __construct(
        private readonly CommunityContext $context,
        private readonly PostHtmlSanitizer $sanitizer,
    ) {}

    public function publish(User $user, PostPublishData $data, int $dailyLimit): PostPublishOutcome
    {
        $brand = $this->context->require();
        if (! $user->isCommunityParticipant($brand->id)) {
            return PostPublishOutcome::failed('Bạn cần tham gia cộng đồng trước khi đăng bài.');
        }

        $lock = Cache::lock($this->lockKey($user), 10);
        if (! $lock->get()) {
            return PostPublishOutcome::failed('Bài viết đang được xử lý. Vui lòng thử lại sau một chút.');
        }

        try {
            return DB::transaction(function () use ($user, $data, $dailyLimit, $brand): PostPublishOutcome {
                if ($this->todayPostCount($user, $brand->id) >= $dailyLimit) {
                    return PostPublishOutcome::failed('Bạn đã đạt giới hạn '.$dailyLimit.' bài hôm nay. Bạn có thể đăng lại sau 00:00 (giờ Việt Nam).');
                }
                if (! $this->taxonomyExists($data, $brand->id)) {
                    return PostPublishOutcome::failed('Chủ đề hoặc loại nội dung không thuộc cộng đồng hiện tại.');
                }

                $contentHtml = filled($data->contentHtml) ? $this->sanitizer->sanitize($data->contentHtml) : null;
                $post = Post::create([
                    'brand_id' => $brand->id,
                    'user_id' => $user->id,
                    'title' => blank($data->title) ? null : $data->title,
                    'content' => $data->content,
                    'content_html' => $contentHtml,
                    'content_format' => $contentHtml === null ? 'markdown' : 'html',
                    'pillar' => $data->pillar,
                    'topic_id' => $data->topicId,
                    'subject_id' => $data->subjectId,
                    'post_type_id' => $data->postTypeId,
                    'is_signal' => false,
                ]);
                $post->update(['slug' => $this->slugFor($post)]);

                foreach ($data->imagePaths as $order => $path) {
                    PostImage::create(['post_id' => $post->id, 'path' => $path, 'order_index' => $order]);
                }

                return PostPublishOutcome::published($post);
            });
        } finally {
            $lock->release();
        }
    }

    public function postsToday(User $user): int
    {
        return $this->todayPostCount($user, $this->context->require()->id);
    }

    private function taxonomyExists(PostPublishData $data, int $brandId): bool
    {
        return $this->exists(Topic::class, $data->topicId, $brandId)
            && $this->exists(CommunitySubject::class, $data->subjectId, $brandId)
            && $this->exists(CommunityPostType::class, $data->postTypeId, $brandId);
    }

    /** @param class-string<Topic|CommunitySubject|CommunityPostType> $model */
    private function exists(string $model, ?int $id, int $brandId): bool
    {
        return $id === null || $model::query()->where('brand_id', $brandId)->whereKey($id)->exists();
    }

    private function todayPostCount(User $user, int $brandId): int
    {
        $start = Carbon::now('Asia/Ho_Chi_Minh')->startOfDay()->utc();
        $end = Carbon::now('Asia/Ho_Chi_Minh')->endOfDay()->utc();

        return Post::withTrashed()
            ->where('brand_id', $brandId)
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$start, $end])
            ->count();
    }

    private function slugFor(Post $post): string
    {
        $base = Str::slug($post->title ?: Str::limit($post->content, 60, '')) ?: 'bai-viet';

        return $base.'-'.$post->id;
    }

    private function lockKey(User $user): string
    {
        return 'compose-post-day:'.$this->context->require()->id.':'.$user->id.':'.now('Asia/Ho_Chi_Minh')->format('Y-m-d');
    }
}
