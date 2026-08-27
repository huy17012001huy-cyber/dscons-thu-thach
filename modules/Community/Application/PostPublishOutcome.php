<?php

declare(strict_types=1);

namespace Modules\Community\Application;

use App\Models\Post;

final readonly class PostPublishOutcome
{
    private function __construct(
        public ?Post $post,
        public ?string $error,
    ) {}

    public static function published(Post $post): self
    {
        return new self($post, null);
    }

    public static function failed(string $error): self
    {
        return new self(null, $error);
    }
}
