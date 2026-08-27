<?php

namespace App\Http\Controllers;

use App\Models\ChallengeTask;
use App\Models\Expedition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ChallengeRewardController extends Controller
{
    private const DAILY_DOWNLOAD_LIMIT = 3;

    public function download(string $slug, int $day): Response
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        $expedition = Expedition::where('slug', $slug)->firstOrFail();

        $task = ChallengeTask::where('expedition_id', $expedition->id)
            ->where('day_number', $day)
            ->firstOrFail();

        if (! $task->reward_file_path) {
            abort(404);
        }

        // Authorization: admin pass-through, member phải approved + có submission đã duyệt
        if (! $user->is_admin) {
            $isApprovedMember = $expedition->members()
                ->where('user_id', $user->id)
                ->whereIn('status', ['approved', 'paid'])
                ->whereNull('kicked_at')
                ->exists();
            if (! $isApprovedMember) {
                abort(403);
            }

            $hasApprovedSubmission = DB::table('challenge_task_completions')
                ->where('challenge_task_id', $task->id)
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->exists();
            if (! $hasApprovedSubmission) {
                abort(403);
            }
        }

        if (! Storage::disk('local')->exists($task->reward_file_path)) {
            abort(404);
        }

        // Rate limit: max 3 lần/ngày/user/task. Admin không bị giới hạn.
        if (! $user->is_admin) {
            $key = "reward_dl:{$user->id}:{$task->id}:".today()->format('Y-m-d');
            $count = (int) Cache::get($key, 0);
            if ($count >= self::DAILY_DOWNLOAD_LIMIT) {
                return redirect()->route('challenge.show', $expedition->slug)
                    ->with('toast', [
                        'message' => 'Bấm tải 1 lần là về máy rồi mà? Có trục trặc gì thì nhắn admin group nha',
                        'type' => 'error',
                    ]);
            }
            Cache::put($key, $count + 1, now()->endOfDay());
        }

        $originalExt = pathinfo($task->reward_file_path, PATHINFO_EXTENSION);
        $displayName = $task->reward_file_label ?: basename($task->reward_file_path);
        // Đảm bảo tên hiển thị có extension đúng — nếu label admin nhập thiếu .ext, thêm vô
        if ($originalExt && pathinfo($displayName, PATHINFO_EXTENSION) === '') {
            $displayName .= '.'.$originalExt;
        }

        return Storage::disk('local')->download($task->reward_file_path, $displayName, [
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }
}
