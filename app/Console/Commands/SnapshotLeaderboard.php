<?php

namespace App\Console\Commands;

use App\Models\LeaderboardSnapshot;
use App\Models\User;
use App\Models\XpTransaction;
use Illuminate\Console\Command;

class SnapshotLeaderboard extends Command
{
    protected $signature = 'aip:snapshot-leaderboard';
    protected $description = 'Create weekly/monthly leaderboard snapshots';

    public function handle(): void
    {
        $now = now();
        $weekKey = $now->format('Y-\WW');
        $monthKey = $now->format('Y-m');

        foreach (['week' => $weekKey, 'month' => $monthKey] as $period => $key) {
            $since = $period === 'week' ? $now->copy()->startOfWeek() : $now->copy()->startOfMonth();

            $users = User::select('users.id')
                ->selectRaw('COALESCE(SUM(xp_transactions.amount), 0) as xp_earned')
                ->leftJoin('xp_transactions', function ($j) use ($since) {
                    $j->on('users.id', '=', 'xp_transactions.user_id')
                      ->where('xp_transactions.created_at', '>=', $since);
                })
                ->groupBy('users.id')
                ->orderByDesc('xp_earned')
                ->get();

            $rank = 1;
            foreach ($users as $u) {
                $prev = LeaderboardSnapshot::where('user_id', $u->id)
                    ->where('period', $period)
                    ->where('period_key', '!=', $key)
                    ->orderByDesc('snapshot_date')
                    ->first();

                $rankChange = $prev ? ($prev->rank - $rank) : 0;

                LeaderboardSnapshot::updateOrCreate(
                    ['user_id' => $u->id, 'period' => $period, 'period_key' => $key],
                    ['xp_earned' => $u->xp_earned, 'rank' => $rank, 'rank_change' => $rankChange, 'snapshot_date' => $now->toDateString()]
                );
                $rank++;
            }
        }

        $this->info('Leaderboard snapshots updated.');
    }
}
