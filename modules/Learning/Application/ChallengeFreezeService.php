<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

use App\Core\CommunityContext;
use App\Models\Expedition;
use App\Models\User;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class ChallengeFreezeService
{
    public function __construct(private readonly CommunityContext $context) {}

    public function freeze(Expedition $challenge, User $actor, int $fromDay, CarbonInterface $until): ?Expedition
    {
        $this->assertCurrentCommunity($challenge);

        if (! $actor->isCommunityAdmin($challenge->brand_id)) {
            return null;
        }
        if ($fromDay > $challenge->required_days) {
            throw new DomainException('The freeze day must be within the challenge duration.');
        }
        if ($until->lessThanOrEqualTo(now())) {
            throw new DomainException('The freeze end time must be in the future.');
        }

        return DB::transaction(function () use ($challenge, $fromDay, $until): Expedition {
            $challenge->newQuery()
                ->whereKey($challenge->id)
                ->lockForUpdate()
                ->firstOrFail()
                ->update([
                    'freeze_from_day' => $fromDay,
                    'freeze_starts_at' => $challenge->freeze_starts_at ?? now(),
                    'freeze_ends_at' => $until,
                ]);

            return $challenge->refresh();
        });
    }

    public function clear(Expedition $challenge, User $actor): ?Expedition
    {
        $this->assertCurrentCommunity($challenge);

        if (! $actor->isCommunityAdmin($challenge->brand_id)) {
            return null;
        }

        return DB::transaction(function () use ($challenge): Expedition {
            $challenge->newQuery()
                ->whereKey($challenge->id)
                ->lockForUpdate()
                ->firstOrFail()
                ->update([
                    'freeze_from_day' => null,
                    'freeze_starts_at' => null,
                    'freeze_ends_at' => null,
                ]);

            return $challenge->refresh();
        });
    }

    private function assertCurrentCommunity(Expedition $challenge): void
    {
        $brand = $this->context->current();

        if ($brand && $challenge->brand_id !== $brand->id) {
            throw new AuthorizationException('Challenge does not belong to the current community.');
        }
    }
}
