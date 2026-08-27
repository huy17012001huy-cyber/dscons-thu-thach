<?php

declare(strict_types=1);

namespace App\Core\Integrations;

use App\Core\CommunityContext;
use App\Models\Expedition;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class BotQueryService
{
    public function __construct(private readonly CommunityContext $context) {}

    public function findUser(string $query): ?User
    {
        $brand = $this->context->require();
        $nameQuery = '%'.mb_strtolower($query).'%';

        return User::query()
            ->where(function ($users) use ($query, $nameQuery): void {
                $users->where('email', $query)
                    ->orWhere('username', $query)
                    ->orWhereRaw('LOWER(name) LIKE ?', [$nameQuery]);
            })
            ->where(function ($users) use ($brand): void {
                $users->where('is_admin', true)
                    ->orWhereHas('brandRoles', fn ($roles) => $roles->where('brands.id', $brand->id));
            })
            ->first();
    }

    public function findChallenge(?string $query): ?Expedition
    {
        $brand = $this->context->require();
        $challenges = Expedition::query()->where('brand_id', $brand->id);

        if (filled($query)) {
            return $challenges
                ->where(fn ($challenge) => $challenge
                    ->where('slug', $query)
                    ->orWhere('id', is_numeric($query) ? $query : 0))
                ->first();
        }

        return $challenges->where('status', 'active')->first();
    }

    /** @return array<string, mixed> */
    public function member(User $user): array
    {
        $membership = $user->membership;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'level' => $user->level,
            'xp' => $user->xp,
            'aip' => $user->aip,
            'streak' => $user->streak,
            'class' => $user->class,
            'class_label' => $user->class_label,
            'da_count' => $user->da_count,
            'membership' => $membership ? [
                'status' => $membership->status,
                'plan' => $membership->plan,
                'expires_at' => $membership->expires_at?->toDateString(),
                'trial_ends_at' => $membership->trial_ends_at?->toDateString(),
            ] : null,
            'last_active_at' => $user->last_active_at?->toDateTimeString(),
            'profile_url' => route('profile', $user->username ?? $user->id),
        ];
    }

    /** @return array<string, mixed> */
    public function challengeProgress(User $user, Expedition $challenge): array
    {
        $member = $challenge->members()->where('user_id', $user->id)->first();
        if (! $member) {
            return [
                'user' => $user->name,
                'challenge' => $challenge->title,
                'status' => 'not_enrolled',
                'message' => $user->name.' chưa tham gia challenge này',
            ];
        }

        $tasks = $challenge->tasks()->orderBy('day_number')->get();
        $completions = DB::table('challenge_task_completions')
            ->whereIn('challenge_task_id', $tasks->pluck('id'))
            ->where('user_id', $user->id)
            ->get();
        $completedCount = $completions->count();
        $rejectedCount = $completions->where('status', 'rejected')->count();
        $currentDay = $this->currentDay($challenge, $member->personal_starts_at, $completedCount);

        return [
            'user' => $user->name,
            'username' => $user->username,
            'challenge' => $challenge->title,
            'status' => $member->status,
            'kicked' => (bool) $member->kicked_at,
            'started_at' => $member->personal_starts_at?->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i'),
            'current_day' => $currentDay,
            'total_days' => $challenge->required_days,
            'completed' => $completedCount,
            'approved' => $completions->where('status', 'approved')->count(),
            'pending' => $completions->where('status', 'pending')->count(),
            'rejected' => $rejectedCount,
            'late' => $completions->where('is_late', true)->count(),
            'missed' => $this->missedDays($challenge, $member->personal_starts_at, $completedCount, $rejectedCount),
            'total_tasks' => $tasks->count(),
            'progress_pct' => $tasks->isNotEmpty() ? round(($completedCount - $rejectedCount) / $tasks->count() * 100) : 0,
            'challenge_url' => route('challenge.show', $challenge->slug),
        ];
    }

    /** @return array<string, mixed> */
    public function pendingSubmissions(Expedition $challenge): array
    {
        $submissions = DB::table('challenge_task_completions')
            ->join('challenge_tasks', 'challenge_tasks.id', '=', 'challenge_task_completions.challenge_task_id')
            ->join('users', 'users.id', '=', 'challenge_task_completions.user_id')
            ->whereIn('challenge_task_completions.challenge_task_id', $challenge->tasks()->pluck('id'))
            ->where('challenge_task_completions.status', 'pending')
            ->select(
                'challenge_task_completions.id as completion_id',
                'users.name', 'users.username',
                'challenge_tasks.day_number', 'challenge_tasks.title as task_title',
                'challenge_task_completions.evidence',
                'challenge_task_completions.is_late',
                'challenge_task_completions.created_at',
            )
            ->orderByDesc('challenge_task_completions.created_at')
            ->limit(20)
            ->get();

        return [
            'challenge' => $challenge->title,
            'pending_count' => $submissions->count(),
            'submissions' => $submissions->all(),
        ];
    }

    private function currentDay(Expedition $challenge, mixed $startedAt, int $completedCount): int
    {
        if (! $startedAt) {
            return 0;
        }

        $timeDay = min((int) floor($startedAt->diffInHours(now()) / 24) + 1, $challenge->required_days);

        return min($timeDay, $completedCount + 1);
    }

    private function missedDays(Expedition $challenge, mixed $startedAt, int $completedCount, int $rejectedCount): int
    {
        if (! $startedAt) {
            return 0;
        }

        $expiredDays = min((int) floor($startedAt->diffInHours(now()) / 24), $challenge->required_days);

        return max(0, $expiredDays - ($completedCount - $rejectedCount));
    }
}
