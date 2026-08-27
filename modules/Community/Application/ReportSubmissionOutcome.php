<?php

declare(strict_types=1);

namespace Modules\Community\Application;

enum ReportSubmissionOutcome
{
    case Reported;
    case AlreadyReported;
    case RateLimited;
    case OwnContent;
}
