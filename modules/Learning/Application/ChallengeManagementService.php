<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

use App\Core\CommunityContext;
use App\Models\Expedition;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class ChallengeManagementService
{
    public function __construct(private readonly CommunityContext $context) {}

    /** @param array<string, mixed> $attributes */
    public function save(?Expedition $challenge, User $actor, array $attributes): ?Expedition
    {
        $brand = $this->context->require();

        if (! $actor->isCommunityAdmin($brand->id)) {
            return null;
        }
        if ($challenge && $challenge->brand_id !== $brand->id) {
            throw new AuthorizationException('Challenge does not belong to the current community.');
        }

        $attributes['brand_id'] = $brand->id;

        return DB::transaction(function () use ($challenge, $attributes, $actor): Expedition {
            if ($challenge) {
                $challenge->update($attributes);

                return $challenge->refresh();
            }

            return Expedition::create([...$attributes, 'created_by' => $actor->id]);
        });
    }

    public function delete(Expedition $challenge, User $actor): bool
    {
        $brand = $this->context->require();

        if (! $actor->isCommunityAdmin($brand->id)) {
            return false;
        }
        if ($challenge->brand_id !== $brand->id) {
            throw new AuthorizationException('Challenge does not belong to the current community.');
        }

        $coverPath = $challenge->cover_path;
        DB::transaction(function () use ($challenge): bool {
            $challenge->delete();

            return true;
        });

        if ($coverPath && str_starts_with($coverPath, 'challenge/covers/')) {
            Storage::disk('public')->delete($coverPath);
        }

        return true;
    }

    public function deleteReplacedCover(?string $oldCoverPath, ?string $newCoverPath): void
    {
        if ($oldCoverPath && $oldCoverPath !== $newCoverPath && str_starts_with($oldCoverPath, 'challenge/covers/')) {
            Storage::disk('public')->delete($oldCoverPath);
        }
    }
}
