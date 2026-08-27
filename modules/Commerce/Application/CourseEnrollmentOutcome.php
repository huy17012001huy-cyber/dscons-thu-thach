<?php

declare(strict_types=1);

namespace Modules\Commerce\Application;

enum CourseEnrollmentOutcome
{
    case Active;
    case PendingPayment;
    case AlreadyActive;
    case AlreadyPending;
    case LevelLocked;
    case Unavailable;
}
