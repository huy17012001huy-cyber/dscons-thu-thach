<?php

declare(strict_types=1);

namespace Modules\Community\Application;

final readonly class PostReactionResult
{
    public function __construct(
        public bool $isActive,
        public int $count = 0,
    ) {}
}
