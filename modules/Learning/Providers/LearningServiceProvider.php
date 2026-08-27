<?php

declare(strict_types=1);

namespace Modules\Learning\Providers;

use App\Core\Modules\BaseModuleServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\Learning\Domain\Events\ChallengeEnrollmentRequested;
use Modules\Learning\Domain\Events\ChallengeEnrollmentReviewed;
use Modules\Learning\Domain\Events\ChallengeSubmissionCreated;
use Modules\Learning\Domain\Events\ChallengeSubmissionReviewed;
use Modules\Learning\Domain\Events\ChallengeVideoFeedbackChanged;
use Modules\Learning\Domain\Listeners\HandleChallengeSubmissionReviewed;
use Modules\Learning\Domain\Listeners\NotifyChallengeEnrollmentRequested;
use Modules\Learning\Domain\Listeners\NotifyChallengeEnrollmentReviewed;
use Modules\Learning\Domain\Listeners\NotifyChallengeSubmissionCreated;
use Modules\Learning\Domain\Listeners\NotifyChallengeVideoFeedbackChanged;

final class LearningServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'Learning';

    public function boot(): void
    {
        parent::boot();

        Event::listen(ChallengeEnrollmentRequested::class, NotifyChallengeEnrollmentRequested::class);
        Event::listen(ChallengeEnrollmentReviewed::class, NotifyChallengeEnrollmentReviewed::class);
        Event::listen(ChallengeSubmissionCreated::class, NotifyChallengeSubmissionCreated::class);
        Event::listen(ChallengeSubmissionReviewed::class, HandleChallengeSubmissionReviewed::class);
        Event::listen(ChallengeVideoFeedbackChanged::class, NotifyChallengeVideoFeedbackChanged::class);
    }
}
