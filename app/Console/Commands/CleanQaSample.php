<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CleanQaSample extends Command
{
    protected $signature = 'qa:clean-sample {--force : Delete the [TEST] QA dataset}';

    protected $description = 'List or remove the local/staging [TEST] QA dataset';

    public function handle(): int
    {
        if (! app()->environment(['local', 'staging', 'testing'])) {
            $this->error('This command is only available in local, staging, or testing environments.');

            return self::FAILURE;
        }

        $userIds = $this->qaUserIds();
        $eventIds = $this->eventIds($userIds);
        $expeditionIds = $this->expeditionIds($userIds);
        $courseIds = $this->courseIds();
        $productIds = $this->productIds();
        $topicIds = $this->topicIds();

        $counts = [
            'users' => $userIds->count(),
            'events' => $eventIds->count(),
            'expeditions' => $expeditionIds->count(),
            'courses' => $courseIds->count(),
            'products' => $productIds->count(),
            'topics' => $topicIds->count(),
        ];

        if (! $this->option('force')) {
            $this->table(['Dataset', 'Records'], collect($counts)
                ->map(fn (int $count, string $table) => [$table, $count])
                ->values()
                ->all());
            $this->line('Dry run only. Pass --force to remove this QA dataset.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($userIds, $eventIds, $expeditionIds, $courseIds, $productIds, $topicIds): void {
            DB::table('sessions')->whereIn('user_id', $userIds)->delete();
            DB::table('notifications')->where('type', 'qa-test-notification')->delete();

            if ($eventIds->isNotEmpty()) {
                DB::table('event_registrations')->whereIn('event_id', $eventIds)->delete();
                DB::table('events')->whereIn('id', $eventIds)->delete();
            }

            if ($expeditionIds->isNotEmpty()) {
                DB::table('challenge_tasks')->whereIn('expedition_id', $expeditionIds)->delete();
                DB::table('expedition_members')->whereIn('expedition_id', $expeditionIds)->delete();
                DB::table('expeditions')->whereIn('id', $expeditionIds)->delete();
            }

            if ($courseIds->isNotEmpty()) {
                DB::table('courses')->whereIn('id', $courseIds)->delete();
            }

            if ($productIds->isNotEmpty()) {
                DB::table('digital_products')->whereIn('id', $productIds)->delete();
            }

            if ($topicIds->isNotEmpty()) {
                DB::table('topics')->whereIn('id', $topicIds)->delete();
            }

            DB::table('users')->whereIn('id', $userIds)->delete();
        });

        $this->info('QA dataset removed.');

        return self::SUCCESS;
    }

    /** @return Collection<int, int> */
    private function qaUserIds(): Collection
    {
        return DB::table('users')
            ->where('source', 'qa-test')
            ->orWhereIn('email', [
                'qa-admin@example.test',
                'qa-member@example.test',
                'qa-guest@example.test',
                'qa-unverified@example.test',
                'qa-banned@example.test',
            ])
            ->pluck('id');
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @return Collection<int, int>
     */
    private function eventIds(Collection $userIds): Collection
    {
        return DB::table('events')
            ->where('title', 'like', '[TEST]%')
            ->when($userIds->isNotEmpty(), fn ($query) => $query->orWhereIn('created_by', $userIds))
            ->pluck('id');
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @return Collection<int, int>
     */
    private function expeditionIds(Collection $userIds): Collection
    {
        return DB::table('expeditions')
            ->where('title', 'like', '[TEST]%')
            ->when($userIds->isNotEmpty(), fn ($query) => $query->orWhereIn('created_by', $userIds))
            ->pluck('id');
    }

    /** @return Collection<int, int> */
    private function courseIds(): Collection
    {
        return DB::table('courses')->where('title', 'like', '[TEST]%')->pluck('id');
    }

    /** @return Collection<int, int> */
    private function productIds(): Collection
    {
        return DB::table('digital_products')->where('title', 'like', '[TEST]%')->pluck('id');
    }

    /** @return Collection<int, int> */
    private function topicIds(): Collection
    {
        return DB::table('topics')->where('name', 'like', '[TEST]%')->pluck('id');
    }
}
