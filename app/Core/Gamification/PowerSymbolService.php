<?php

declare(strict_types=1);

namespace App\Core\Gamification;

use App\Core\CommunityContext;
use App\Models\PowerSymbol;
use App\Models\User;

final class PowerSymbolService
{
    private const LEVEL_THRESHOLDS = [1 => 10, 2 => 30, 3 => 60, 4 => 100];

    public function __construct(private readonly CommunityContext $context) {}

    public function addFragments(User $user, string $pillar, int $fragments): void
    {
        $symbol = PowerSymbol::firstOrCreate(
            ['user_id' => $user->id, 'pillar' => $pillar, 'brand_id' => $this->context->current()?->id],
            ['level' => 0, 'fragments' => 0],
        );
        $symbol->increment('fragments', $fragments);

        foreach (self::LEVEL_THRESHOLDS as $level => $threshold) {
            if ($symbol->fragments >= $threshold && $symbol->level < $level) {
                $symbol->update(['level' => $level]);
            }
        }
    }
}
