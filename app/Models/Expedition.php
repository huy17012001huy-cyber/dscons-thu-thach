<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Models\Concerns\HasBrand;

class Expedition extends Model {
    use HasBrand;
    protected $fillable = [
        'title','slug','description','cover_path','boss_name','difficulty','required_days','max_members',
        'created_by','leader_id','status','deposit_aip','starts_at','ends_at','price','access_tier','brand_id','is_featured',
        'freeze_from_day','freeze_starts_at','freeze_ends_at',
    ];
    protected $casts = [
        'starts_at'=>'datetime','ends_at'=>'datetime','price'=>'decimal:2',
        'freeze_starts_at'=>'datetime','freeze_ends_at'=>'datetime','is_featured'=>'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $exp) {
            if (!$exp->slug) {
                $exp->slug = Str::slug($exp->title) ?: 'challenge';
            }
        });
    }

    public function getRouteKeyName(): string { return 'slug'; }

    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function leader(): BelongsTo { return $this->belongsTo(User::class, 'leader_id'); }
    public function members(): HasMany { return $this->hasMany(ExpeditionMember::class); }
    public function checkins(): HasMany { return $this->hasMany(ExpeditionCheckin::class); }
    public function tasks(): HasMany { return $this->hasMany(ChallengeTask::class); }
    public function events(): HasMany { return $this->hasMany(Event::class); }

    public function activeMembersCount(): int { return $this->members()->whereNull('kicked_at')->count(); }
    public function uniqueClassCount(): int {
        return $this->members()->whereNull('kicked_at')->distinct('class_at_join')->count('class_at_join');
    }
    public function getXpBonusMultiplier(): float {
        $classes = $this->uniqueClassCount();
        return match(true) { $classes >= 5 => 1.5, $classes >= 3 => 1.2, default => 1.0 };
    }
    public function getDifficultyLabelAttribute(): string {
        return match($this->difficulty) { 'normal'=>'Thường','hard'=>'Khó','chaos'=>'Hỗn loạn',default=>$this->difficulty };
    }
    public function getDifficultyColorAttribute(): string {
        return match($this->difficulty) { 'normal'=>'blue','hard'=>'amber','chaos'=>'red',default=>'gray' };
    }

    /**
     * Get freeze-adjusted elapsed hours for a member.
     * Subtracts the overlap of (member's elapsed window) with (freeze window) for ALL members,
     * so every member's clock pauses during the freeze, not just those reaching freeze_from_day.
     */
    public function getAdjustedHoursForMember(ExpeditionMember $member): int
    {
        if (!$member->personal_starts_at) return 0;

        $hours = (int) $member->personal_starts_at->diffInHours(now());

        if (!$this->freeze_starts_at || !$this->freeze_ends_at) {
            return $hours;
        }

        $effectiveStart = $member->personal_starts_at->greaterThan($this->freeze_starts_at)
            ? $member->personal_starts_at
            : $this->freeze_starts_at;
        $effectiveEnd = now()->lessThan($this->freeze_ends_at) ? now() : $this->freeze_ends_at;

        if ($effectiveEnd->greaterThan($effectiveStart)) {
            $hours -= (int) $effectiveStart->diffInHours($effectiveEnd);
        }

        return max(0, $hours);
    }

    /**
     * Current unlocked day for a member (freeze-aware, cascade-aware).
     * Day N unlocks when Day N-1's deadline passes — so a deadline override on an earlier
     * day also delays the unlock of later days, not just their deadline.
     * During freeze: hard cap at freeze_from_day - 1 (frozen day not unlocked).
     */
    public function getCurrentDayForMember(ExpeditionMember $member): int
    {
        if (!$member->personal_starts_at) return 0;

        $now = now();
        $day = 0;
        for ($n = 1; $n <= $this->required_days; $n++) {
            $unlockAt = $n === 1
                ? $member->personal_starts_at
                : $this->getDeadlineForMemberAtDay($member, $n - 1);
            if ($now->greaterThanOrEqualTo($unlockAt)) {
                $day = $n;
            } else {
                break;
            }
        }

        if ($this->isFreezeActive() && $day >= $this->freeze_from_day) {
            return $this->freeze_from_day - 1;
        }

        return $day;
    }

    /**
     * Number of fully expired 24h windows (freeze-aware).
     * During freeze: cap expired days so frozen days don't count as missed.
     */
    public function getExpiredDaysForMember(ExpeditionMember $member): int
    {
        $hours = $this->getAdjustedHoursForMember($member);
        $days = min((int) floor($hours / 24), $this->required_days);

        if ($this->isFreezeActive() && $days >= $this->freeze_from_day - 1) {
            return $this->freeze_from_day - 1;
        }

        return $days;
    }

    /**
     * Whether a freeze is currently active.
     */
    public function isFreezeActive(): bool
    {
        return $this->freeze_from_day
            && $this->freeze_starts_at
            && $this->freeze_ends_at
            && now()->between($this->freeze_starts_at, $this->freeze_ends_at);
    }

    /**
     * Per-member wall-clock deadline for a given day.
     *
     * Default: personal_starts_at + cumulative duration_hours.
     *
     * If any earlier day (including this one) carries deadline_override_at, that value is an ANCHOR.
     * Rounded to the member's own start hour:minute (VN tz) on/after the anchor, then cascaded forward
     * 24h per subsequent day so later days also shift, otherwise a Day N extension collides with Day N+1's
     * natural deadline and they expire at the same moment.
     *
     * Freeze shift (applies AFTER cascade): if the final deadline lands during the freeze window,
     * push it past freeze_ends so nobody is marked late during the rest period.
     */
    public function getDeadlineForMemberAtDay(ExpeditionMember $member, int $dayNumber): \Carbon\Carbon
    {
        // Precompute cumulative hours map once per (Expedition instance) — avoid O(T²) re-scan
        // when getDeadlineForMemberAtDay is called inside outer foreach loops.
        if (!isset($this->_hoursToDayMap)) {
            $map = [0 => 0];
            $cum = 0;
            foreach ($this->tasks->sortBy('day_number') as $t) {
                $cum += (int) $t->duration_hours;
                $map[(int) $t->day_number] = $cum;
            }
            $this->_hoursToDayMap = $map;
            $this->_hoursToDayMaxDay = empty($map) ? 0 : max(array_keys($map));
        }
        $hoursToDay = function (int $n): int {
            if ($n <= 0) return 0;
            return $this->_hoursToDayMap[$n] ?? ($n * 24);
        };

        // Freeze shift applies to NATURAL deadline only — admin pinpoint overrides are final.
        // Shift = freeze overlap with member elapsed window; 0 if member started at/after freeze ended.
        $natural = $member->personal_starts_at->copy()->addHours($hoursToDay($dayNumber));
        if ($this->freeze_starts_at && $this->freeze_ends_at
            && $natural->greaterThanOrEqualTo($this->freeze_starts_at)
            && $member->personal_starts_at->lessThan($this->freeze_ends_at)
        ) {
            $effFreezeStart = $member->personal_starts_at->greaterThan($this->freeze_starts_at)
                ? $member->personal_starts_at
                : $this->freeze_starts_at;
            $shiftHours = (int) $effFreezeStart->diffInHours($this->freeze_ends_at);
            $natural = $natural->copy()->addHours($shiftHours);
        }

        $tz = 'Asia/Ho_Chi_Minh';
        $memberStart = $member->personal_starts_at->copy()->setTimezone($tz);
        $result = $natural;

        foreach ($this->tasks as $t) {
            if (!$t->deadline_override_at || $t->day_number > $dayNumber) continue;

            $anchorVN = $t->deadline_override_at->copy()->setTimezone($tz);
            $rounded = $anchorVN->copy()->setTime($memberStart->hour, $memberStart->minute, 0);
            if ($rounded->lessThan($anchorVN)) {
                $rounded->addDay();
            }

            $extraHours = $hoursToDay($dayNumber) - $hoursToDay($t->day_number);
            $cascaded = $rounded->utc()->addHours($extraHours);
            if ($cascaded->greaterThan($result)) {
                $result = $cascaded;
            }
        }

        // Per-member deadline overrides: admin can pin a specific day's deadline for one member
        // (cascades 24h/day forward like task-level overrides, but isolated to this member only).
        if (!empty($member->deadline_overrides) && is_array($member->deadline_overrides)) {
            foreach ($member->deadline_overrides as $overrideDay => $overrideAt) {
                $overrideDay = (int) $overrideDay;
                if ($overrideDay < 1 || $overrideDay > $dayNumber) continue;
                $extraHours = $hoursToDay($dayNumber) - $hoursToDay($overrideDay);
                $cascaded = \Carbon\Carbon::parse($overrideAt)->addHours($extraHours);
                if ($cascaded->greaterThan($result)) {
                    $result = $cascaded;
                }
            }
        }

        return $result;
    }

    public function isTaskLateForMember(ExpeditionMember $member, int $dayNumber): bool
    {
        if (!$member->personal_starts_at) return false;

        $contestEnd = $this->getContestEndForMemberAtDay($member, $dayNumber);
        if ($contestEnd) {
            return now()->greaterThan($contestEnd);
        }

        return now()->greaterThan($this->getDeadlineForMemberAtDay($member, $dayNumber));
    }

    /**
     * Contest mini-game close time, decoupled from day-unlock cascade.
     * Only set when the task is a contest with contest_duration_hours configured.
     * Returns null otherwise → callers fall back to day deadline.
     */
    public function getContestEndForMemberAtDay(ExpeditionMember $member, int $dayNumber): ?\Carbon\Carbon
    {
        if (!$member->personal_starts_at) return null;

        $task = $this->tasks->where('day_number', $dayNumber)->first();
        if (!$task || !$task->is_contest || !$task->contest_duration_hours) return null;

        $dayOpensAt = $dayNumber === 1
            ? $member->personal_starts_at->copy()
            : $this->getDeadlineForMemberAtDay($member, $dayNumber - 1);

        return $dayOpensAt->copy()->addHours((int) $task->contest_duration_hours);
    }

    public function start(): void {
        if ($this->status !== 'open') return;
        $this->update([
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addDays($this->required_days),
        ]);
    }

    public function complete(): void {
        if ($this->status !== 'active') return;
        $this->update(['status' => 'completed']);
    }

    public function fail(): void {
        if ($this->status !== 'active') return;
        $this->update(['status' => 'failed']);
    }
}
