<?php

declare(strict_types=1);

namespace Modules\Learning\Domain\Events;

use App\Models\Expedition;
use App\Models\User;
use Illuminate\Queue\SerializesModels;

final class ChallengeEnrollmentRequested
{
    use SerializesModels;

    public function __construct(
        public readonly Expedition $challenge,
        public readonly User $learner,
    ) {}
}
