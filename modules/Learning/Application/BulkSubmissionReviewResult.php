<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

use App\Models\ChallengeTaskCompletion;
use Illuminate\Database\Eloquent\Collection;

/** @phpstan-type CompletionCollection Collection<int, ChallengeTaskCompletion> */
final readonly class BulkSubmissionReviewResult
{
    /**
     * @param  CompletionCollection  $completions
     * @param  CompletionCollection  $awardableCompletions
     */
    public function __construct(
        public Collection $completions,
        public Collection $awardableCompletions,
    ) {}
}
