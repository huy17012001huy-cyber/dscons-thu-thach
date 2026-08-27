<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

enum ChallengeSubmissionOutcome: string
{
    case Submitted = 'submitted';
    case Resubmitted = 'resubmitted';
    case Frozen = 'frozen';
    case NotEnrolled = 'not_enrolled';
    case NotUnlocked = 'not_unlocked';
    case TaskLocked = 'task_locked';
    case AlreadySubmitted = 'already_submitted';
    case MissingEvidence = 'missing_evidence';
    case NotRejected = 'not_rejected';
}
