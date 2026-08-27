<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

enum CourseLessonCompletionOutcome
{
    case Completed;
    case CourseCompleted;
    case AlreadyCompleted;
    case NotEnrolled;
    case Locked;
    case Unavailable;
}
