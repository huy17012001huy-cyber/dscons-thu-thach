<?php

namespace App\Http\Controllers;

use App\Models\Expedition;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BotApiController extends Controller
{
    /**
     * Verify bot API token from request header.
     */
    private function verify(Request $request): bool
    {
        $token = config('services.bot.api_token');
        $authHeader = $request->header('Authorization');

        // Use hash_equals to prevent timing attacks
        return $token && hash_equals('Bearer '.$token, $authHeader ?? '');
    }

    /**
     * GET /api/bot/member?q=email_or_username
     * Lookup member info: name, level, XP, class, membership, streak
     */
    public function lookupMember(Request $request): JsonResponse
    {
        if (! $this->verify($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $q = $request->input('q', '');
        if (blank($q)) {
            return response()->json(['error' => 'Missing q parameter'], 400);
        }

        $user = User::where('email', $q)
            ->orWhere('username', $q)
            ->orWhere('name', 'ilike', '%'.$q.'%')
            ->first();

        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $membership = $user->membership;

        return response()->json([
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
        ]);
    }

    /**
     * GET /api/bot/challenge-progress?q=email_or_username&challenge=slug_or_id
     * Check member's challenge progress
     */
    public function challengeProgress(Request $request): JsonResponse
    {
        if (! $this->verify($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $q = $request->input('q', '');
        if (blank($q)) {
            return response()->json(['error' => 'Missing q parameter'], 400);
        }

        $user = User::where('email', $q)
            ->orWhere('username', $q)
            ->orWhere('name', 'ilike', '%'.$q.'%')
            ->first();

        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Find challenge — by slug, id, or default to first active
        $challengeParam = $request->input('challenge');
        if ($challengeParam) {
            $expedition = Expedition::where('slug', $challengeParam)
                ->orWhere('id', is_numeric($challengeParam) ? $challengeParam : 0)
                ->first();
        } else {
            $expedition = Expedition::where('status', 'active')->first();
        }

        if (! $expedition) {
            return response()->json(['error' => 'Challenge not found'], 404);
        }

        $member = $expedition->members()
            ->where('user_id', $user->id)
            ->first();

        if (! $member) {
            return response()->json([
                'user' => $user->name,
                'challenge' => $expedition->title,
                'status' => 'not_enrolled',
                'message' => $user->name.' chưa tham gia challenge này',
            ]);
        }

        $tasks = $expedition->tasks()->orderBy('day_number')->get();
        $taskIds = $tasks->pluck('id');

        $completions = DB::table('challenge_task_completions')
            ->whereIn('challenge_task_id', $taskIds)
            ->where('user_id', $user->id)
            ->get();

        $completedCount = $completions->count();
        $approvedCount = $completions->where('status', 'approved')->count();
        $rejectedCount = $completions->where('status', 'rejected')->count();
        $pendingCount = $completions->where('status', 'pending')->count();
        $lateCount = $completions->where('is_late', true)->count();

        // Current day calculation
        $currentDay = 0;
        if ($member->personal_starts_at) {
            $hoursElapsed = $member->personal_starts_at->diffInHours(now());
            $timeDay = min((int) floor($hoursElapsed / 24) + 1, $expedition->required_days);
            $currentDay = min($timeDay, $completedCount + 1);
        }

        $missedDays = 0;
        if ($member->personal_starts_at) {
            $hoursElapsed = $member->personal_starts_at->diffInHours(now());
            $expiredDays = min((int) floor($hoursElapsed / 24), $expedition->required_days);
            $missedDays = max(0, $expiredDays - ($completedCount - $rejectedCount));
        }

        return response()->json([
            'user' => $user->name,
            'username' => $user->username,
            'challenge' => $expedition->title,
            'status' => $member->status,
            'kicked' => (bool) $member->kicked_at,
            'started_at' => $member->personal_starts_at?->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i'),
            'current_day' => $currentDay,
            'total_days' => $expedition->required_days,
            'completed' => $completedCount,
            'approved' => $approvedCount,
            'pending' => $pendingCount,
            'rejected' => $rejectedCount,
            'late' => $lateCount,
            'missed' => $missedDays,
            'total_tasks' => $tasks->count(),
            'progress_pct' => $tasks->count() > 0 ? round(($completedCount - $rejectedCount) / $tasks->count() * 100) : 0,
            'challenge_url' => route('challenge.show', $expedition->slug),
        ]);
    }

    /**
     * GET /api/bot/pending-submissions?challenge=slug_or_id
     * List pending submissions for admin review
     */
    public function pendingSubmissions(Request $request): JsonResponse
    {
        if (! $this->verify($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $challengeParam = $request->input('challenge');
        $expedition = $challengeParam
            ? Expedition::where('slug', $challengeParam)->orWhere('id', is_numeric($challengeParam) ? $challengeParam : 0)->first()
            : Expedition::where('status', 'active')->first();

        if (! $expedition) {
            return response()->json(['error' => 'Challenge not found'], 404);
        }

        $taskIds = $expedition->tasks()->pluck('id');

        $submissions = DB::table('challenge_task_completions')
            ->join('challenge_tasks', 'challenge_tasks.id', '=', 'challenge_task_completions.challenge_task_id')
            ->join('users', 'users.id', '=', 'challenge_task_completions.user_id')
            ->whereIn('challenge_task_completions.challenge_task_id', $taskIds)
            ->where('challenge_task_completions.status', 'pending')
            ->select(
                'challenge_task_completions.id as completion_id',
                'users.name', 'users.username',
                'challenge_tasks.day_number', 'challenge_tasks.title as task_title',
                'challenge_task_completions.evidence',
                'challenge_task_completions.is_late',
                'challenge_task_completions.created_at'
            )
            ->orderByDesc('challenge_task_completions.created_at')
            ->limit(20)
            ->get();

        return response()->json([
            'challenge' => $expedition->title,
            'pending_count' => $submissions->count(),
            'submissions' => $submissions,
        ]);
    }
}
