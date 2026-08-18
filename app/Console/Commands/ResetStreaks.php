<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetStreaks extends Command
{
    protected $signature = 'aip:reset-streaks';
    protected $description = 'Reset streak for users inactive for 48+ hours';

    public function handle(): void
    {
        $cutoff = now()->subHours(48);

        $count = User::where('streak', '>', 0)
            ->where(function ($q) use ($cutoff) {
                $q->where('last_active_at', '<', $cutoff)
                  ->orWhereNull('last_active_at');
            })
            ->update(['streak' => 0]);

        $this->info("Reset {$count} streaks.");
    }
}
