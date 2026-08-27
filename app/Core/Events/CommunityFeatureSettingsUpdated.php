<?php

declare(strict_types=1);

namespace App\Core\Events;

final readonly class CommunityFeatureSettingsUpdated
{
    public function __construct(
        public int $brandId,
        public bool $hasCv,
        public bool $hasRecruitment,
    ) {}
}
