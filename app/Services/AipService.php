<?php

namespace App\Services;

use App\Models\AipTransaction;
use App\Models\User;

class AipService
{
    public function earn(User $user, int $amount, string $reason, $reference = null): void
    {
        AipTransaction::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'type' => 'earn',
            'reason' => $reason,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
        ]);

        $user->increment('aip', $amount);
    }

    public function spend(User $user, int $amount, string $reason, $reference = null): void
    {
        if ($user->aip < $amount) {
            throw new \RuntimeException('Không đủ AIP. Cần ' . $amount . ', hiện có ' . $user->aip);
        }

        AipTransaction::create([
            'user_id' => $user->id,
            'amount' => -$amount,
            'type' => 'spend',
            'reason' => $reason,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
        ]);

        $user->decrement('aip', $amount);
    }
}
