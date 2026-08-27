<?php

declare(strict_types=1);

namespace Modules\Learning\Domain\Events;

final class ChallengeSubmissionCreated
{
    public function __construct(
        public readonly int $challengeId,
        public readonly int $taskId,
        public readonly int $learnerId,
        public readonly bool $isContest,
        public readonly bool $isLate,
    ) {}
}
