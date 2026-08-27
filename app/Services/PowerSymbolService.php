<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\CommunityContext;
use App\Models\PowerSymbol;
use App\Models\User;

class PowerSymbolService
{
    private const LEVEL_THRESHOLDS = [1 => 10, 2 => 30, 3 => 60, 4 => 100];

    public function __construct(private readonly CommunityContext $context) {}

    public function addFragments(User $user, string $pillar, int $fragments): void
    {
        $brandId = $this->context->current()?->id;
        $symbol = PowerSymbol::firstOrCreate(
            ['user_id' => $user->id, 'pillar' => $pillar, 'brand_id' => $brandId],
            ['level' => 0, 'fragments' => 0]
        );

        $symbol->increment('fragments', $fragments);

        // Check level up
        foreach (self::LEVEL_THRESHOLDS as $level => $threshold) {
            if ($symbol->fragments >= $threshold && $symbol->level < $level) {
                $symbol->update(['level' => $level]);
            }
        }
    }
}
