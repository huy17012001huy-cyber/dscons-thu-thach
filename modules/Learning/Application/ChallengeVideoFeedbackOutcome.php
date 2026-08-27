<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

enum ChallengeVideoFeedbackOutcome: string
{
    case Submitted = 'submitted';
    case NotEnrolled = 'not_enrolled';
    case MissingUrl = 'missing_url';
}
