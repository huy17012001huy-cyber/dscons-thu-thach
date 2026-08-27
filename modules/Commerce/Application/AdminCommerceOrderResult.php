<?php

declare(strict_types=1);

namespace Modules\Commerce\Application;

use App\Models\User;

final readonly class AdminCommerceOrderResult
{
    public function __construct(
        public User $user,
        public string $label,
        public ?string $url = null,
    ) {}
}
