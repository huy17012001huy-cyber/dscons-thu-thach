<?php

declare(strict_types=1);

namespace Modules\Community\Application;

use App\Models\Comment;

final readonly class CommentSubmissionResult
{
    private function __construct(
        public ?Comment $comment,
        public bool $isRateLimited,
    ) {}

    public static function submitted(Comment $comment): self
    {
        return new self($comment, false);
    }

    public static function rateLimited(): self
    {
        return new self(null, true);
    }
}
