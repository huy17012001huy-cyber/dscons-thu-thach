<?php

namespace App\Console\Commands;

use App\Core\Gamification\XpService;
use App\Models\Expedition;
use App\Notifications\GenericNotification;
use Illuminate\Console\Command;

class ProcessChallenges extends Command
{
    protected $signature = 'aip:process-challenges';

    protected $description = 'Auto-complete/fail expired challenges and kick inactive members';

    public function handle(): void
    {
        $xp = app(XpService::class);

        $expired = Expedition::where('status', 'active')
            ->where('ends_at', '<', now())
            ->with(['members.user', 'creator', 'leader'])
            ->get();

        foreach ($expired as $ch) {
            $activeMembers = $ch->members()->whereNull('kicked_at')->get();
            $allCompleted = $activeMembers->every(fn ($m) => $m->consecutive_missed_days < 3);

            if ($allCompleted && $activeMembers->count() >= 2) {
                $ch->complete();
                foreach ($activeMembers as $member) {
                    $xp->award($member->user, 'expedition_complete', $ch->getXpBonusMultiplier(), 'Hoàn thành Challenge: '.$ch->title, $ch);
                    $member->update(['completed_at' => now()]);
                }
                if ($ch->leader) {
                    $xp->award($ch->leader, 'expedition_captain', 1.0, 'Leader hoàn thành: '.$ch->title, $ch);
                }
                $this->info("Completed: {$ch->title}");
            } else {
                $ch->fail();
                $this->info("Failed: {$ch->title}");
            }
        }

        // Check missed days based on 24h windows, not calendar days
        $active = Expedition::where('status', 'active')->with(['members.user', 'tasks'])->get();
        foreach ($active as $ch) {
            $taskIds = $ch->tasks->pluck('id');
            $members = $ch->members()->whereNull('kicked_at')->whereNotNull('personal_starts_at')->get();

            foreach ($members as $member) {
                // How many 24h windows have fully elapsed (freeze-aware)?
                $expiredDays = $ch->getExpiredDaysForMember($member);

                // Exclude days whose per-member deadline is still in the future (override cascade can push any day)
                $pendingDays = 0;
                foreach ($ch->tasks as $t) {
                    if ($t->day_number > $expiredDays) {
                        continue;
                    }
                    if ($ch->getDeadlineForMemberAtDay($member, $t->day_number)->greaterThan(now())) {
                        $pendingDays++;
                    }
                }
                $dueDays = $expiredDays - $pendingDays;

                // How many tasks completed (not rejected)?
                $completedCount = \DB::table('challenge_task_completions')
                    ->whereIn('challenge_task_id', $taskIds)
                    ->where('user_id', $member->user_id)
                    ->where('status', '!=', 'rejected')
                    ->count();

                // Missed = due windows without a submission
                $missedDays = max(0, $dueDays - $completedCount);

                $member->update(['consecutive_missed_days' => $missedDays]);

                if ($missedDays >= 3 && ! $member->miss_warned) {
                    $member->update(['miss_warned' => true]);
                    $member->user->notify(new GenericNotification('⚠', 'Bạn đã miss '.$missedDays.' ngày trong Challenge "'.$ch->title.'". Hãy nộp bài sớm!'));
                    $this->info("Warned {$member->user->name} in {$ch->title} (missed {$missedDays})");
                }
            }
        }
    }
}
