<?php

declare(strict_types=1);

namespace Modules\Community\Application;

use App\Core\CommunityContext;
use App\Core\Events\CommunityFeatureSettingsUpdated;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class CommunityFeatureSettingsService
{
    public function __construct(private readonly CommunityContext $context) {}

    public function update(Brand $community, User $actor, bool $hasCv, bool $hasRecruitment): Brand
    {
        $brand = $this->context->require();
        if (! $community->is($brand) || ! $actor->isCommunityAdmin($brand->id)) {
            throw new AuthorizationException('Community admin access is required.');
        }

        return DB::transaction(function () use ($community, $hasCv, $hasRecruitment): Brand {
            $community->update(['has_cv' => $hasCv, 'has_recruitment' => $hasRecruitment]);
            $updated = $community->fresh() ?? $community;
            DB::afterCommit(fn () => Event::dispatch(new CommunityFeatureSettingsUpdated(
                $updated->id,
                $hasCv,
                $hasRecruitment,
            )));

            return $updated;
        });
    }
}
