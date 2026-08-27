<?php

declare(strict_types=1);

namespace Modules\Learning\Domain\Events;

use App\Models\ChallengeTaskCompletion;
use App\Models\Expedition;
use Illuminate\Queue\SerializesModels;

final class ChallengeSubmissionReviewed
{
    use SerializesModels;

    public function __construct(
        public readonly Expedition $challenge,
        public readonly ChallengeTaskCompletion $completion,
        public readonly bool $shouldAwardXp,
        public readonly string $status,
        public readonly ?string $note,
    ) {}
}
