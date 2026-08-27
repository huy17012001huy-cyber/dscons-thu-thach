<?php

declare(strict_types=1);

namespace Modules\Learning\Providers;

use App\Core\Modules\BaseModuleServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\Learning\Domain\Events\ChallengeEnrollmentRequested;
use Modules\Learning\Domain\Listeners\NotifyChallengeEnrollmentRequested;

final class LearningServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'Learning';

    public function boot(): void
    {
        parent::boot();

        Event::listen(ChallengeEnrollmentRequested::class, NotifyChallengeEnrollmentRequested::class);
    }
}
