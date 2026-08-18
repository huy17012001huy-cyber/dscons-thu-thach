<?php

namespace App\Console\Commands;

use App\Models\PillarStat;
use App\Models\Post;
use Illuminate\Console\Command;

class RecalcPillarStats extends Command
{
    protected $signature = 'aip:recalc-pillar-stats';
    protected $description = 'Recalculate pillar post counts and burning zone';

    public function handle(): void
    {
        $since = now()->subDays(7);
        $pillars = ['offer', 'traffic', 'conversion', 'delivery', 'continuity'];
        $counts = [];
        $total = 0;

        foreach ($pillars as $p) {
            $count = Post::where('pillar', $p)->where('created_at', '>=', $since)->count();
            $counts[$p] = $count;
            $total += $count;
        }

        $minPillar = array_keys($counts, min($counts))[0];

        foreach ($pillars as $p) {
            $pct = $total > 0 ? round($counts[$p] / $total * 100, 2) : 20;
            $isBurning = ($p === $minPillar && $total > 0);

            PillarStat::updateOrCreate(
                ['pillar' => $p],
                [
                    'post_count_7d' => $counts[$p],
                    'post_pct' => $pct,
                    'is_burning' => $isBurning,
                    'burning_started_at' => $isBurning ? (PillarStat::where('pillar', $p)->value('burning_started_at') ?? now()) : null,
                    'last_calculated_at' => now(),
                ]
            );
        }

        $this->info("Pillar stats updated. Burning: {$minPillar}");
    }
}
