<?php

declare(strict_types=1);

namespace Modules\Community\Application;

enum QaAnswerOutcome
{
    case Answered;
    case AlreadyAnswered;
    case RateLimited;
}
