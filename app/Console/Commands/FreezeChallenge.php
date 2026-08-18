<?php

namespace App\Console\Commands;

use App\Models\Expedition;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Pause or resume a challenge timer for a given day forward.
 *
 * When frozen:
 *   - Members currently at freeze_from_day or beyond have their timer paused
 *   - No late penalty during the freeze window
 *   - Members still climbing will reach the freeze day naturally;
 *     they are only frozen once they arrive
 *
 * Usage:
 *   php artisan challenge:freeze chinh-phuc-100-khach-hang-dau-tien --from-day=13 --until="2026-04-20 06:00"
 *   php artisan challenge:freeze 1 --from-day=13 --until=2026-04-20
 *   php artisan challenge:freeze 1 --clear
 */
class FreezeChallenge extends Command
{
    protected $signature = 'challenge:freeze
        {expedition : Expedition slug or ID}
        {--from-day= : Day number at which freeze applies (e.g. 13)}
        {--until= : Freeze ends at (YYYY-MM-DD or YYYY-MM-DD HH:MM, Asia/Ho_Chi_Minh)}
        {--starts= : Freeze begins at (optional, defaults to now)}
        {--clear : Clear existing freeze}';

    protected $description = 'Pause or resume a challenge timer for a given day forward';

    private const TZ = 'Asia/Ho_Chi_Minh';

    public function handle(): int
    {
        $exp = $this->resolveExpedition();
        if (!$exp) return self::FAILURE;

        if ($this->option('clear')) {
            return $this->clearFreeze($exp);
        }

        return $this->applyFreeze($exp);
    }

    private function resolveExpedition(): ?Expedition
    {
        $key = $this->argument('expedition');
        $exp = is_numeric($key)
            ? Expedition::find($key)
            : Expedition::where('slug', $key)->first();

        if (!$exp) {
            $this->error("Expedition not found: {$key}");
        }
        return $exp;
    }

    private function clearFreeze(Expedition $exp): int
    {
        $exp->update([
            'freeze_from_day' => null,
            'freeze_starts_at' => null,
            'freeze_ends_at' => null,
        ]);
        $this->info("✓ Freeze cleared for [{$exp->title}]");
        return self::SUCCESS;
    }

    private function applyFreeze(Expedition $exp): int
    {
        $fromDay = (int) $this->option('from-day');
        $until = $this->option('until');

        if (!$fromDay || !$until) {
            $this->error('Required: --from-day and --until (or use --clear)');
            return self::FAILURE;
        }

        if ($fromDay < 1 || $fromDay > $exp->required_days) {
            $this->error("--from-day must be between 1 and {$exp->required_days}");
            return self::FAILURE;
        }

        try {
            $endsAt = Carbon::parse($until, self::TZ)->utc();
        } catch (\Throwable $e) {
            $this->error("Invalid --until: {$until}");
            return self::FAILURE;
        }

        $starts = $this->option('starts');
        if ($starts) {
            $startsAt = Carbon::parse($starts, self::TZ)->utc();
        } elseif ($exp->freeze_starts_at && $exp->freeze_ends_at && $exp->freeze_starts_at->lessThan(now())) {
            // Extending an existing freeze — preserve original start
            $startsAt = $exp->freeze_starts_at;
            $this->line("  (preserving existing start; pass --starts to override)");
        } else {
            $startsAt = now();
        }

        if ($endsAt->lessThanOrEqualTo($startsAt)) {
            $this->error('Freeze end must be after freeze start');
            return self::FAILURE;
        }

        $exp->update([
            'freeze_from_day' => $fromDay,
            'freeze_starts_at' => $startsAt,
            'freeze_ends_at' => $endsAt,
        ]);

        $this->info("✓ Freeze set for [{$exp->title}]");
        $this->line("  From day: {$fromDay}");
        $this->line('  Starts:   '.$startsAt->timezone(self::TZ)->format('Y-m-d H:i').' ('.self::TZ.')');
        $this->line('  Ends:     '.$endsAt->timezone(self::TZ)->format('Y-m-d H:i').' ('.self::TZ.')');

        return self::SUCCESS;
    }
}
