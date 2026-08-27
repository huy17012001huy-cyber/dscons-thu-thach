<?php

declare(strict_types=1);

namespace Modules\Community\Application;

use Carbon\CarbonInterface;

final readonly class EventManagementData
{
    public function __construct(
        public string $title,
        public ?string $description,
        public string $eventType,
        public string $format,
        public CarbonInterface $startsAt,
        public CarbonInterface $endsAt,
        public string $status,
        public ?int $courseId = null,
        public ?int $expeditionId = null,
        public ?string $meetingUrl = null,
        public ?string $location = null,
        public ?int $capacity = null,
    ) {}
}
