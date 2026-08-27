<?php

declare(strict_types=1);

namespace Modules\Community\Application;

use App\Core\CommunityContext;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class CommunityTopicService
{
    public function __construct(private readonly CommunityContext $context) {}

    public function save(?int $topicId, User $actor, CommunityTopicData $data): Topic
    {
        $brand = $this->context->require();
        if (! $actor->isCommunityAdmin($brand->id)) {
            throw new AuthorizationException('Community admin access is required.');
        }

        return DB::transaction(function () use ($topicId, $data, $brand): Topic {
            $topic = $topicId === null
                ? new Topic(['brand_id' => $brand->id])
                : $this->lockedTopic($topicId, $brand->id);
            $slugInUse = Topic::query()
                ->where('brand_id', $brand->id)
                ->where('slug', $data->slug)
                ->when($topic->exists, fn ($query) => $query->whereKeyNot($topic->id))
                ->exists();
            if ($slugInUse) {
                throw new InvalidArgumentException('Slug đã được dùng trong community này.');
            }

            $topic->fill([
                'name' => $data->name,
                'emoji' => $data->emoji,
                'slug' => $data->slug,
                'sort_order' => $data->sortOrder,
                'is_active' => $data->isActive,
            ])->save();

            return $topic;
        });
    }

    public function toggleActive(int $topicId, User $actor): bool
    {
        $brand = $this->context->require();
        if (! $actor->isCommunityAdmin($brand->id)) {
            throw new AuthorizationException('Community admin access is required.');
        }

        return DB::transaction(function () use ($topicId, $brand): bool {
            $topic = $this->lockedTopic($topicId, $brand->id);
            $topic->update(['is_active' => ! $topic->is_active]);

            return true;
        });
    }

    public function delete(int $topicId, User $actor): bool
    {
        $brand = $this->context->require();
        if (! $actor->isCommunityAdmin($brand->id)) {
            throw new AuthorizationException('Community admin access is required.');
        }

        return DB::transaction(function () use ($topicId, $brand): bool {
            $this->lockedTopic($topicId, $brand->id)->delete();

            return true;
        });
    }

    private function lockedTopic(int $topicId, int $brandId): Topic
    {
        return Topic::query()
            ->where('brand_id', $brandId)
            ->whereKey($topicId)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
