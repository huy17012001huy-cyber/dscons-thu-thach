<?php

declare(strict_types=1);

namespace Modules\Community\Application;

final readonly class CommunityProfileData
{
    public function __construct(
        public string $name,
        public ?string $tagline,
        public ?string $description,
        public ?string $guideContent,
        public ?string $rulesContent,
        public ?string $logoPath,
        public ?string $bannerPath,
    ) {}
}
