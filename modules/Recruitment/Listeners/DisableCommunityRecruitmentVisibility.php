<?php

declare(strict_types=1);

namespace Modules\Recruitment\Listeners;

use App\Core\Events\CommunityFeatureSettingsUpdated;
use App\Models\EngineerCv;
use App\Models\EngineerProfile;

final class DisableCommunityRecruitmentVisibility
{
    public function handle(CommunityFeatureSettingsUpdated $event): void
    {
        if ($event->hasCv) {
            return;
        }

        EngineerCv::withoutGlobalScopes()
            ->where('brand_id', $event->brandId)
            ->update(['status' => 'draft']);
        EngineerProfile::withoutGlobalScopes()
            ->where('brand_id', $event->brandId)
            ->update(['is_searchable' => false]);
    }
}
