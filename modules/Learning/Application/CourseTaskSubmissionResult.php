<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

final readonly class CourseTaskSubmissionResult
{
    public function __construct(
        public bool $accepted,
        public CourseLessonCompletionOutcome $completion,
    ) {}
}
