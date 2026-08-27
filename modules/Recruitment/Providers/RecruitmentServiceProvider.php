<?php

declare(strict_types=1);

namespace Modules\Recruitment\Providers;

use App\Core\Events\CommunityFeatureSettingsUpdated;
use App\Core\Modules\BaseModuleServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\Recruitment\Listeners\DisableCommunityRecruitmentVisibility;

final class RecruitmentServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'Recruitment';

    public function boot(): void
    {
        parent::boot();

        Event::listen(CommunityFeatureSettingsUpdated::class, DisableCommunityRecruitmentVisibility::class);
    }
}
