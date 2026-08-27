<?php

declare(strict_types=1);

namespace App\Core\Gamification;

use App\Models\AipTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

final class AipService
{
    public function earn(User $user, int $amount, string $reason, ?Model $reference = null): void
    {
        AipTransaction::create(['user_id' => $user->id, 'amount' => $amount, 'type' => 'earn', 'reason' => $reason, 'reference_type' => $reference ? $reference::class : null, 'reference_id' => $reference?->getKey()]);
        $this->updateBalance($user, $amount);
    }

    public function spend(User $user, int $amount, string $reason, ?Model $reference = null): void
    {
        $stats = app(CommunityStatsService::class)->for($user);
        $balance = $stats ? (int) $stats->aip : (int) $user->aip;
        if ($balance < $amount) {
            throw new \RuntimeException('Không đủ AIP. Cần '.$amount.', hiện có '.$balance);
        }
        AipTransaction::create(['user_id' => $user->id, 'amount' => -$amount, 'type' => 'spend', 'reason' => $reason, 'reference_type' => $reference ? $reference::class : null, 'reference_id' => $reference?->getKey()]);
        $this->updateBalance($user, -$amount);
    }

    private function updateBalance(User $user, int $amount): void
    {
        $stats = app(CommunityStatsService::class)->for($user);
        if ($stats) {
            $stats->increment('aip', $amount);
            $stats->refresh();
            $user->setAttribute('aip', (int) $stats->aip);

            return;
        }
        $user->increment('aip', $amount);
    }
}
