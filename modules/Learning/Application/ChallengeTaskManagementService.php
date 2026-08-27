<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

use App\Core\CommunityContext;
use App\Models\ChallengeTask;
use App\Models\Expedition;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ChallengeTaskManagementService
{
    public function __construct(private readonly CommunityContext $context) {}

    public function find(int $taskId, User $actor): ?ChallengeTask
    {
        return $this->taskForCurrentCommunity($taskId, $actor);
    }

    /** @param array<string, mixed> $attributes */
    public function save(
        int $challengeId,
        ?int $taskId,
        User $actor,
        array $attributes,
    ): ?ChallengeTask {
        $challenge = $this->challengeForCurrentCommunity($challengeId, $actor);
        if (! $challenge) {
            return null;
        }

        return DB::transaction(function () use ($challenge, $taskId, $attributes): ChallengeTask {
            $attributes['expedition_id'] = $challenge->id;

            if ($taskId) {
                $task = ChallengeTask::query()
                    ->whereKey($taskId)
                    ->where('expedition_id', $challenge->id)
                    ->lockForUpdate()
                    ->firstOrFail();
                $task->update($attributes);

                return $task->refresh();
            }

            return ChallengeTask::create($attributes);
        });
    }

    public function saveRewardFile(ChallengeTask $task, User $actor, string $path): ?ChallengeTask
    {
        $scopedTask = $this->taskForCurrentCommunity($task->id, $actor);
        if (! $scopedTask) {
            return null;
        }

        return DB::transaction(function () use ($scopedTask, $path): ChallengeTask {
            $scopedTask->update(['reward_file_path' => $path]);

            return $scopedTask->refresh();
        });
    }

    public function removeRewardFile(int $taskId, User $actor): ?string
    {
        $task = $this->taskForCurrentCommunity($taskId, $actor);
        if (! $task) {
            return null;
        }

        $path = $task->reward_file_path;
        DB::transaction(function () use ($task): void {
            $task->update(['reward_file_path' => null, 'reward_file_label' => null]);
        });

        return $path;
    }

    public function delete(int $taskId, User $actor): ?ChallengeTask
    {
        $task = $this->taskForCurrentCommunity($taskId, $actor);
        if (! $task) {
            return null;
        }

        DB::transaction(function () use ($task): void {
            $task->delete();
        });

        return $task;
    }

    private function taskForCurrentCommunity(int $taskId, User $actor): ?ChallengeTask
    {
        $brand = $this->context->require();
        if (! $actor->isCommunityAdmin($brand->id)) {
            return null;
        }

        return ChallengeTask::query()
            ->whereKey($taskId)
            ->whereHas('expedition', function ($query) use ($brand): void {
                $query->where('brand_id', $brand->id);
            })
            ->first();
    }

    private function challengeForCurrentCommunity(int $challengeId, User $actor): ?Expedition
    {
        $brand = $this->context->require();
        if (! $actor->isCommunityAdmin($brand->id)) {
            return null;
        }

        return Expedition::query()
            ->whereKey($challengeId)
            ->where('brand_id', $brand->id)
            ->first();
    }
}
