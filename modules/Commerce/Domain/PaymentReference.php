<?php

declare(strict_types=1);

namespace Modules\Commerce\Domain;

final readonly class PaymentReference
{
    /** @param array<string, int> $attributes */
    public function __construct(
        public string $type,
        public array $attributes,
    ) {}
}
