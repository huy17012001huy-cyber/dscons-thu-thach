<?php

declare(strict_types=1);

namespace Modules\Learning\Domain\Events;

final class ChallengeVideoFeedbackChanged
{
    public function __construct(
        public readonly int $challengeId,
        public readonly int $learnerId,
        public readonly string $status,
        public readonly ?string $note = null,
    ) {}
}
