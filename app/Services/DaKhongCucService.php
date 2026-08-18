<?php

namespace App\Services;

use App\Models\DaKhongCuc;
use App\Models\DaKhongCucLog;
use App\Models\User;
use App\Notifications\GenericNotification;

class DaKhongCucService
{
    public function award(User $user, int $delta, string $reason, ?User $awardedBy = null): void
    {
        $record = DaKhongCuc::firstOrCreate(
            ['user_id' => $user->id],
            ['total_count' => 0]
        );

        $record->increment('total_count', $delta);

        DaKhongCucLog::create([
            'user_id' => $user->id,
            'delta' => $delta,
            'reason' => $reason,
            'awarded_by' => $awardedBy?->id,
        ]);

        $user->notify(new GenericNotification('💎', "Bạn nhận được {$delta} Đá Không Cực: {$reason}"));
    }
}
