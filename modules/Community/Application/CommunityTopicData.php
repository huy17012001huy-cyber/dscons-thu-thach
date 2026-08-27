<?php

declare(strict_types=1);

namespace Modules\Community\Application;

final readonly class CommunityTopicData
{
    public function __construct(
        public string $name,
        public ?string $emoji,
        public string $slug,
        public int $sortOrder,
        public bool $isActive,
    ) {}
}
