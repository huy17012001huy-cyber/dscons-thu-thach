<?php

declare(strict_types=1);

namespace Modules\Recruitment\Providers;

use App\Core\Events\CommunityFeatureSettingsUpdated;
use App\Core\Modules\BaseModuleServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\Recruitment\Application\DeterministicCandidateMatcher;
use Modules\Recruitment\Application\DeterministicJobDescriptionParser;
use Modules\Recruitment\Contracts\CandidateMatcher;
use Modules\Recruitment\Contracts\JobDescriptionParser;
use Modules\Recruitment\Listeners\DisableCommunityRecruitmentVisibility;

final class RecruitmentServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'Recruitment';

    public function register(): void
    {
        parent::register();

        $this->app->bind(CandidateMatcher::class, DeterministicCandidateMatcher::class);
        $this->app->bind(JobDescriptionParser::class, DeterministicJobDescriptionParser::class);
    }

    public function boot(): void
    {
        parent::boot();

        Event::listen(CommunityFeatureSettingsUpdated::class, DisableCommunityRecruitmentVisibility::class);
    }
}
