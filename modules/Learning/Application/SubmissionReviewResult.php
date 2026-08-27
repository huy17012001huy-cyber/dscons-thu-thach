<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

use App\Models\ChallengeTaskCompletion;

final readonly class SubmissionReviewResult
{
    public function __construct(
        public ChallengeTaskCompletion $completion,
        public bool $shouldAwardXp,
    ) {}
}
