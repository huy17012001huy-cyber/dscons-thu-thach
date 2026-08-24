<?php

namespace App\Console\Commands;

use App\Models\PillarStat;
use App\Models\Post;
use App\Models\Brand;
use Illuminate\Console\Command;

class RecalcPillarStats extends Command
{
    protected $signature = 'aip:recalc-pillar-stats';
    protected $description = 'Recalculate pillar post counts and burning zone';

    public function handle(): void
    {
        $since = now()->subDays(7);
        $pillars = ['offer', 'traffic', 'conversion', 'delivery', 'continuity'];

        Brand::query()->each(function (Brand $brand) use ($since, $pillars): void {
            $counts = [];
            $total = 0;

            foreach ($pillars as $pillar) {
                $count = Post::withoutGlobalScopes()
                    ->where('brand_id', $brand->id)
                    ->where('pillar', $pillar)
                    ->where('created_at', '>=', $since)
                    ->count();
                $counts[$pillar] = $count;
                $total += $count;
            }

            $minPillar = array_keys($counts, min($counts))[0];

            foreach ($pillars as $pillar) {
                $pct = $total > 0 ? round($counts[$pillar] / $total * 100, 2) : 20;
                $isBurning = $pillar === $minPillar && $total > 0;
                $previous = PillarStat::withoutGlobalScopes()
                    ->where('brand_id', $brand->id)
                    ->where('pillar', $pillar)
                    ->first();

                PillarStat::withoutGlobalScopes()->updateOrCreate(
                    ['brand_id' => $brand->id, 'pillar' => $pillar],
                    [
                        'post_count_7d' => $counts[$pillar],
                        'post_pct' => $pct,
                        'is_burning' => $isBurning,
                        'burning_started_at' => $isBurning ? ($previous?->burning_started_at ?? now()) : null,
                        'last_calculated_at' => now(),
                    ]
                );
            }

            $this->info("{$brand->slug}: burning {$minPillar}");
        });
    }
}
