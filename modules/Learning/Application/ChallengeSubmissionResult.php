<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

use App\Models\ChallengeTask;

final readonly class ChallengeSubmissionResult
{
    public function __construct(
        public ChallengeSubmissionOutcome $outcome,
        public ?ChallengeTask $task = null,
        public bool $isLate = false,
    ) {}
}
