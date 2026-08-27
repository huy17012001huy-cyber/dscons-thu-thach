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
    case NotContest = 'not_contest';
    case SubmissionClosed = 'submission_closed';
    case MainSubmissionMissing = 'main_submission_missing';
    case MainSubmissionPending = 'main_submission_pending';
    case MainSubmissionRejected = 'main_submission_rejected';
    case ContestEntryPending = 'contest_entry_pending';
    case ContestSubmitted = 'contest_submitted';
}
