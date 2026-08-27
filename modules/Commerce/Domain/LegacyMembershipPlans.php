<?php

declare(strict_types=1);

namespace Modules\Commerce\Domain;

final class LegacyMembershipPlans
{
    /**
     * Historical plans retained for old payment references.
     *
     * @var array<int, array{weeks: int, price_per_week: int, label: string, save: int}>
     */
    public const PLANS = [
        1 => ['weeks' => 1, 'price_per_week' => 500000, 'label' => '1 week', 'save' => 0],
        4 => ['weeks' => 4, 'price_per_week' => 350000, 'label' => '4 weeks', 'save' => 30],
        5 => ['weeks' => 5, 'price_per_week' => 300000, 'label' => '5 weeks', 'save' => 40],
        52 => ['weeks' => 52, 'price_per_week' => 250000, 'label' => '52 weeks (1 year)', 'save' => 50],
    ];
}
