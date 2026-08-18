<?php

namespace App\Console\Commands;

use App\Models\CommunityChallenge;
use App\Models\Comment;
use App\Models\ExpeditionCheckin;
use App\Models\Post;
use Illuminate\Console\Command;

class UpdateChallengeProgress extends Command
{
    protected $signature = 'aip:update-challenge';
    protected $description = 'Update active community challenge progress';

    public function handle(): void
    {
        $challenge = CommunityChallenge::whereNull('completed_at')
            ->where('week_end', '>=', now()->toDateString())
            ->first();

        if (!$challenge) {
            $this->info('No active challenge.');
            return;
        }

        $start = $challenge->week_start->startOfDay();
        $end = $challenge->week_end->endOfDay();

        $count = match ($challenge->target_type) {
            'post_count' => Post::whereBetween('created_at', [$start, $end])->count(),
            'comment_count' => Comment::whereBetween('created_at', [$start, $end])->count(),
            'expedition_checkin' => ExpeditionCheckin::whereBetween('created_at', [$start, $end])->count(),
            default => 0,
        };

        $challenge->update(['current_value' => $count]);

        if ($count >= $challenge->target_value && !$challenge->completed_at) {
            $challenge->update(['completed_at' => now()]);
            $this->info("Challenge completed: {$challenge->title}");
        } else {
            $this->info("Challenge progress: {$count}/{$challenge->target_value}");
        }
    }
}
