<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

enum ChallengeCheckinOutcome: string
{
    case CheckedIn = 'checked_in';
    case NotEnrolled = 'not_enrolled';
    case AlreadyCheckedIn = 'already_checked_in';
}
