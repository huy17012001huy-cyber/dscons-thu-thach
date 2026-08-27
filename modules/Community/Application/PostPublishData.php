<?php

declare(strict_types=1);

namespace Modules\Community\Application;

final readonly class PostPublishData
{
    /** @param list<string> $imagePaths */
    public function __construct(
        public string $content,
        public string $pillar,
        public ?string $title = null,
        public ?string $contentHtml = null,
        public ?int $topicId = null,
        public ?int $subjectId = null,
        public ?int $postTypeId = null,
        public array $imagePaths = [],
    ) {}
}
