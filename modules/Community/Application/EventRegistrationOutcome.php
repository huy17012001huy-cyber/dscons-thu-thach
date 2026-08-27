<?php

declare(strict_types=1);

namespace Modules\Community\Application;

enum EventRegistrationOutcome
{
    case Registered;
    case AlreadyRegistered;
    case NotEligible;
    case Closed;
    case Full;
}
