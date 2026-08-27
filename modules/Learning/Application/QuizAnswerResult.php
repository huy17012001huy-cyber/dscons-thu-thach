<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

final readonly class QuizAnswerResult
{
    public function __construct(
        public string $selectedLetter,
        public bool $isCorrect,
        public bool $wasRecorded,
        public bool $xpAwarded,
    ) {}
}
