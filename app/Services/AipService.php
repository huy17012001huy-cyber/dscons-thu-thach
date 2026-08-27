<?php

namespace App\Services;

use App\Models\AipTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AipService
{
    public function earn(User $user, int $amount, string $reason, ?Model $reference = null): void
    {
        AipTransaction::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'type' => 'earn',
            'reason' => $reason,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->getKey(),
        ]);

        $stats = app(CommunityStatsService::class)->for($user);
        if ($stats) {
            $stats->increment('aip', $amount);
            $stats->refresh();
            $user->setAttribute('aip', (int) $stats->aip);
        } else {
            $user->increment('aip', $amount);
        }
    }

    public function spend(User $user, int $amount, string $reason, ?Model $reference = null): void
    {
        $stats = app(CommunityStatsService::class)->for($user);
        $currentAip = $stats ? (int) $stats->aip : (int) $user->aip;
        if ($currentAip < $amount) {
            throw new \RuntimeException('Không đủ AIP. Cần '.$amount.', hiện có '.$currentAip);
        }

        AipTransaction::create([
            'user_id' => $user->id,
            'amount' => -$amount,
            'type' => 'spend',
            'reason' => $reason,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->getKey(),
        ]);

        if ($stats) {
            $stats->decrement('aip', $amount);
            $stats->refresh();
            $user->setAttribute('aip', (int) $stats->aip);
        } else {
            $user->decrement('aip', $amount);
        }
    }
}
