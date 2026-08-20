<?php

namespace App\Livewire;

use App\Models\ChallengeTask;
use App\Models\Expedition;
use App\Models\ExpeditionCheckin;
use App\Models\ExpeditionMember;
use App\Notifications\GenericNotification;
use App\Services\XpService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ChallengeDetail extends Component
{
    use WithPagination;

    public Expedition $expedition;
    public bool $premiumLocked = false;
    #[Rule('required|min:5|max:1000')]
    public string $checkinContent = '';

    // Pagination for report & submissions
    public int $reportPage = 1;
    public int $submissionPage = 1;
    public string $submissionSearch = '';
    public string $reportSearch = '';
    private const PER_PAGE = 10;

    public function updatedSubmissionSearch(): void { $this->submissionPage = 1; }
    public function updatedReportSearch(): void { $this->reportPage = 1; }

    public function mount(string $slug): void
    {
        $this->expedition = Expedition::with(['leader', 'members.user', 'tasks'])
            ->where('slug', $slug)
            ->orWhere('id', is_numeric($slug) ? $slug : 0)
            ->firstOrFail();
        $user = Auth::user();
        $this->premiumLocked = ($this->expedition->access_tier ?? 'premium') === 'premium'
            && $user
            && !$user->hasPremiumMembership()
            && !$user->isBrandAdmin();
    }

    // ─── Enrollment ─────────────────────────────────────────
    public function requestJoin(): void
    {
        if (!Auth::check()) return;
        $user = Auth::user();

        if ($this->premiumLocked) {
            $this->dispatch('toast', message: 'Challenge này thuộc Premium. Hãy nâng hạng membership để tham gia.', type: 'info');
            return;
        }

        $existingMember = $this->expedition->members()->where('user_id', $user->id)->first();
        if ($existingMember) {
            if ($existingMember->status === 'rejected' || $existingMember->kicked_at) {
                // Rejected or kicked — safe to delete and re-apply
                $existingMember->delete();
            } else {
                $this->dispatch('toast', message: 'Bạn đã đăng ký Challenge này rồi', type: 'error');
                return;
            }
        }

        // Member tạo qua webhook (đã mua qua funnel) → duyệt tự động, bỏ bước admin.
        // Vẫn để personal_starts_at null: user tự bấm "Bắt đầu" khi sẵn sàng.
        $autoApprove = $user->isWebhookCreated();

        // Challenge có giá → chờ chuyển khoản. Webhook SePay tự duyệt khi nhận đủ tiền.
        $price = (int) $this->expedition->price;
        $needsPayment = !$autoApprove && $price > 0;

        ExpeditionMember::create([
            'expedition_id' => $this->expedition->id,
            'user_id' => $user->id,
            'class_at_join' => $user->class,
            'joined_at' => now(),
            'status' => $autoApprove ? 'approved' : ($needsPayment ? 'pending_payment' : 'pending'),
            'approved_at' => $autoApprove ? now() : null,
            'payment_amount' => $needsPayment ? $price : null,
            // approved_by null = hệ thống tự duyệt, không phải admin nào
        ]);

        if ($autoApprove) {
            $this->dispatch('toast', message: 'Đã tham gia Challenge! Bấm "Bắt đầu" khi bạn sẵn sàng.', type: 'success');
            $this->expedition->refresh();
            return;
        }

        // Chờ thanh toán: chưa cần admin làm gì, khỏi bắn thông báo cho họ.
        if ($needsPayment) {
            $this->dispatch('toast', message: 'Quét mã QR để chuyển khoản. Hệ thống tự duyệt ngay khi nhận được tiền.', type: 'success');
            $this->expedition->refresh();
            return;
        }

        // Notify all admins
        \App\Models\User::where('is_admin', true)->each(function ($admin) use ($user) {
            $admin->notify(new GenericNotification(
                '★', $user->name . ' đăng ký tham gia ' . $this->expedition->title,
                route('challenge.show', $this->expedition->slug)
            ));
        });

        $this->dispatch('toast', message: 'Đã gửi yêu cầu tham gia! Vui lòng chờ Admin duyệt.', type: 'success');
        $this->expedition->refresh();
    }

    public function cancelRequest(): void
    {
        if (!Auth::check()) return;
        $member = $this->expedition->members()
            ->where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'pending_payment'])
            ->first();
        if (!$member) return;
        $member->delete();
        $this->dispatch('toast', message: 'Đã rút yêu cầu tham gia', type: 'success');
        $this->expedition->refresh();
    }

    public function approveRequest(int $memberId): void
    {
        if (!Auth::check() || !Auth::user()->is_admin) return;

        $member = ExpeditionMember::where('id', $memberId)
            ->where('expedition_id', $this->expedition->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $member->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
            // personal_starts_at NOT set yet — user must click "Bắt đầu"
        ]);

        $member->user->notify(new GenericNotification(
            '✅', 'Bạn đã được duyệt tham gia ' . $this->expedition->title . '! Bấm "Bắt đầu" khi bạn sẵn sàng.',
            route('challenge.show', $this->expedition->slug)
        ));

        $this->dispatch('toast', message: 'Đã duyệt ' . $member->user->name, type: 'success');
        $this->expedition->refresh();
    }

    public function rejectRequest(int $memberId): void
    {
        if (!Auth::check() || !Auth::user()->is_admin) return;

        $member = ExpeditionMember::where('id', $memberId)
            ->where('expedition_id', $this->expedition->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $member->update(['status' => 'rejected']);

        $member->user->notify(new GenericNotification(
            '❌', 'Yêu cầu tham gia ' . $this->expedition->title . ' đã bị từ chối.',
        ));

        $this->dispatch('toast', message: 'Đã từ chối ' . $member->user->name, type: 'success');
        $this->expedition->refresh();
    }

    // ─── Start challenge (user clicks after approval) ──────
    public function startMyChallenge(): void
    {
        if (!Auth::check()) return;
        $member = $this->expedition->members()
            ->where('user_id', Auth::id())
            ->where('status', 'approved')
            ->whereNull('personal_starts_at')
            ->first();
        if (!$member) return;

        $member->update(['personal_starts_at' => now()]);
        $this->dispatch('toast', message: 'Challenge đã bắt đầu! Chúc bạn chinh phục thành công!', type: 'success');
        $this->expedition->refresh();
    }

    // ─── Check-in ───────────────────────────────────────────
    public function checkin(): void
    {
        if (!Auth::check()) return;
        $this->validate();

        $user = Auth::user();
        $member = $this->getApprovedMember($user->id);
        if (!$member) {
            $this->addError('checkinContent', 'Bạn chưa được duyệt tham gia Challenge này.');
            return;
        }

        $alreadyToday = ExpeditionCheckin::where('expedition_id', $this->expedition->id)
            ->where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->exists();

        if ($alreadyToday) {
            $this->addError('checkinContent', 'Bạn đã check-in hôm nay rồi.');
            return;
        }

        ExpeditionCheckin::create([
            'expedition_id' => $this->expedition->id,
            'user_id' => $user->id,
            'content' => $this->checkinContent,
        ]);

        $member->update(['last_checkin_at' => now(), 'consecutive_missed_days' => 0]);

        app(XpService::class)->award(
            $user, 'expedition_checkin', 1.0,
            'Check-in Challenge: ' . $this->expedition->title,
            $this->expedition
        );

        $this->reset('checkinContent');
        $this->expedition->refresh();
    }

    // ─── Tasks ──────────────────────────────────────────────
    public array $taskEvidence = [];

    public function completeTask(int $taskId): void
    {
        if (!Auth::check()) return;
        $user = Auth::user();

        $task = ChallengeTask::where('id', $taskId)
            ->where('expedition_id', $this->expedition->id)
            ->firstOrFail();

        // Block submit during freeze for tasks at/after freeze_from_day (admin bypass)
        if ($this->expedition->isFreezeActive() && $task->day_number >= $this->expedition->freeze_from_day && !$user->is_admin) {
            $this->dispatch('toast', message: 'Nhiệm vụ tạm dừng trong kỳ nghỉ. Tiếp tục vào ' . $this->expedition->freeze_ends_at->timezone('Asia/Ho_Chi_Minh')->format('d/m'), type: 'warning');
            return;
        }

        $member = $this->getApprovedMember($user->id);
        if (!$member) return;

        $currentDay = $this->expedition->getCurrentDayForMember($member);
        if ($task->day_number > $currentDay) return;

        if ($task->locked_until && now()->lessThan($task->locked_until)) {
            $this->dispatch('toast', message: 'Nhiệm vụ đang tạm khóa, vui lòng quay lại sau.', type: 'warning');
            return;
        }

        if ($task->completedByUsers()->where('user_id', $user->id)->exists()) return;

        $evidence = $this->taskEvidence[$taskId] ?? '';
        if (blank($evidence)) {
            $this->dispatch('toast', message: 'Vui lòng cung cấp bằng chứng hoàn thành!', type: 'error');
            return;
        }

        $late = $this->expedition->isTaskLateForMember($member, $task->day_number);
        $task->completedByUsers()->attach($user->id, [
            'evidence' => $evidence,
            'is_late' => $late,
        ]);

        // XP cộng khi admin approve, KHÔNG cộng khi submit (xem approveSubmission/approveAllPending)

        $this->taskEvidence[$taskId] = '';
        $msg = 'Đã nộp bài ngày ' . $task->day_number . ' — chờ admin duyệt.';
        if ($late) $msg .= ' (Nộp trễ)';
        $this->dispatch('toast', message: $msg, type: $late ? 'warning' : 'success');

        // Notify admin via Telegram
        \App\Services\TelegramService::sendToAdmin(
            "📝 <b>Bài nộp mới</b>\n"
            . "👤 {$user->name}\n"
            . "📋 Ngày {$task->day_number}: {$task->title}\n"
            . ($late ? "⚠️ Nộp trễ\n" : '')
            . "🔗 " . route('challenge.show', $this->expedition->slug)
        );
    }

    // ─── Submit mini-game ứng dụng (contest task only — 1 row/user) ───
    public function submitMiniGame(int $taskId): void
    {
        if (!Auth::check()) return;
        $user = Auth::user();

        $task = ChallengeTask::where('id', $taskId)
            ->where('expedition_id', $this->expedition->id)
            ->where('is_contest', true)
            ->firstOrFail();

        // Block submit during freeze for tasks at/after freeze_from_day (admin bypass)
        if ($this->expedition->isFreezeActive() && $task->day_number >= $this->expedition->freeze_from_day && !$user->is_admin) {
            $this->dispatch('toast', message: 'Nhiệm vụ tạm dừng trong kỳ nghỉ. Tiếp tục vào ' . $this->expedition->freeze_ends_at->timezone('Asia/Ho_Chi_Minh')->format('d/m'), type: 'warning');
            return;
        }

        $member = $this->getApprovedMember($user->id);
        if (!$member) return;

        $currentDay = $this->expedition->getCurrentDayForMember($member);
        if ($task->day_number > $currentDay) return;

        if ($task->locked_until && now()->lessThan($task->locked_until)) {
            $this->dispatch('toast', message: 'Nhiệm vụ đang tạm khóa.', type: 'warning');
            return;
        }

        // Mini-game cứng hạn: hết deadline → không cho nộp tiếp
        if ($this->expedition->isTaskLateForMember($member, $task->day_number)) {
            $this->dispatch('toast', message: 'Mini-game đã hết hạn — không nộp ứng dụng được nữa.', type: 'error');
            return;
        }

        // Phải có bài chính (row 1) ĐÃ được approve mới được tham gia mini-game
        $allRows = \DB::table('challenge_task_completions')
            ->where('challenge_task_id', $taskId)
            ->where('user_id', $user->id)
            ->orderBy('created_at')
            ->get();
        $daySub = $allRows->first();
        if (!$daySub) {
            $this->dispatch('toast', message: 'Hãy nộp bài chính ngày 15 trước.', type: 'error');
            return;
        }
        if ($daySub->status === 'pending') {
            $this->dispatch('toast', message: 'Bài chính đang chờ admin duyệt — đợi được duyệt mới tham gia mini-game được.', type: 'error');
            return;
        }
        if ($daySub->status === 'rejected') {
            $this->dispatch('toast', message: 'Bài chính đang bị từ chối — chỉnh và nộp lại bài chính trước.', type: 'error');
            return;
        }

        // Queue rule: tối đa 1 row đang pending. Approved rows = final (counted). Rejected = replace.
        $miniGameRows = $allRows->slice(1)->values();
        $latestMiniGame = $miniGameRows->last();

        if ($latestMiniGame && $latestMiniGame->status === 'pending') {
            $this->dispatch('toast', message: 'Ứng dụng trước đang chờ duyệt — vui lòng đợi rồi mới nộp tiếp.', type: 'error');
            return;
        }

        $evidence = $this->taskEvidence[$taskId] ?? '';
        if (blank($evidence)) {
            $this->dispatch('toast', message: 'Vui lòng cung cấp bằng chứng ứng dụng.', type: 'error');
            return;
        }

        $late = $this->expedition->isTaskLateForMember($member, $task->day_number);

        if ($latestMiniGame && $latestMiniGame->status === 'rejected') {
            // Replace row rejected — reset về pending, KHÔNG động reject_count
            \DB::table('challenge_task_completions')
                ->where('id', $latestMiniGame->id)
                ->update([
                    'evidence' => $evidence,
                    'status' => 'pending',
                    'is_late' => $late,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'review_note' => null,
                    'updated_at' => now(),
                ]);
        } else {
            // Insert row mới (latest = approved, hoặc chưa có row nào)
            $task->completedByUsers()->attach($user->id, [
                'evidence' => $evidence,
                'is_late' => $late,
            ]);
        }

        $this->taskEvidence[$taskId] = '';
        $this->dispatch('toast', message: 'Đã nộp ứng dụng. Chờ admin duyệt.', type: 'success');

        \App\Services\TelegramService::sendToAdmin(
            "🏆 <b>Mini-game</b>\n"
            . "👤 {$user->name}\n"
            . "📋 Ngày {$task->day_number}: {$task->title}\n"
            . ($late ? "⚠️ Trễ\n" : '')
            . "🔗 " . route('challenge.show', $this->expedition->slug)
        );
    }

    // ─── Resubmit rejected task ───────────────────────────────
    public function resubmitTask(int $taskId): void
    {
        if (!Auth::check()) return;
        $user = Auth::user();

        $task = ChallengeTask::where('id', $taskId)
            ->where('expedition_id', $this->expedition->id)
            ->firstOrFail();

        // Block resubmit during freeze for tasks at/after freeze_from_day (admin bypass)
        if ($this->expedition->isFreezeActive() && $task->day_number >= $this->expedition->freeze_from_day && !$user->is_admin) {
            $this->dispatch('toast', message: 'Nhiệm vụ tạm dừng trong kỳ nghỉ. Tiếp tục vào ' . $this->expedition->freeze_ends_at->timezone('Asia/Ho_Chi_Minh')->format('d/m'), type: 'warning');
            return;
        }

        if ($task->locked_until && now()->lessThan($task->locked_until)) {
            $this->dispatch('toast', message: 'Nhiệm vụ đang tạm khóa, vui lòng quay lại sau.', type: 'warning');
            return;
        }

        // Cho contest task: chỉ resubmit "bài chính" (row đầu tiên rejected). Các ứng dụng cộng sự rejected khác KHÔNG resubmit.
        $existing = \DB::table('challenge_task_completions')
            ->where('challenge_task_id', $taskId)
            ->where('user_id', $user->id)
            ->where('status', 'rejected')
            ->orderBy('created_at')
            ->first();
        if (!$existing) return;

        $evidence = $this->taskEvidence[$taskId] ?? '';
        if (blank($evidence)) {
            $this->dispatch('toast', message: 'Vui lòng cung cấp bằng chứng mới!', type: 'error');
            return;
        }

        $member = $this->getApprovedMember($user->id);
        $late = $this->expedition->isTaskLateForMember($member, $task->day_number);

        \DB::table('challenge_task_completions')
            ->where('id', $existing->id)
            ->update([
                'evidence' => $evidence,
                'status' => 'pending',
                'is_late' => $late,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'review_note' => null,
                'updated_at' => now(),
            ]);

        $this->taskEvidence[$taskId] = '';
        $this->dispatch('toast', message: 'Đã nộp lại bài! Chờ admin duyệt.', type: 'success');
    }

    // ─── Video feedback ────────────────────────────────────────
    public string $videoFeedbackUrl = '';

    public function submitVideoFeedback(): void
    {
        if (!Auth::check()) return;
        $member = $this->getApprovedMember(Auth::id());
        if (!$member) return;
        if (blank($this->videoFeedbackUrl)) {
            $this->dispatch('toast', message: 'Vui lòng paste link video!', type: 'error');
            return;
        }

        $member->update([
            'video_feedback_url' => $this->videoFeedbackUrl,
            'video_feedback_status' => 'pending',
            'video_feedback_at' => now(),
        ]);

        // Notify admins
        \App\Models\User::where('is_admin', true)->each(function ($admin) {
            $admin->notify(new GenericNotification(
                '▶', Auth::user()->name . ' gửi Video Feedback cho ' . $this->expedition->title,
                route('challenge.show', $this->expedition->slug)
            ));
        });

        $this->videoFeedbackUrl = '';
        $this->dispatch('toast', message: 'Đã gửi Video Feedback! Chờ admin duyệt.', type: 'success');
        $this->expedition->refresh();
    }

    public function approveVideoFeedback(int $memberId): void
    {
        if (!Auth::check() || !Auth::user()->is_admin) return;
        $member = ExpeditionMember::findOrFail($memberId);
        $member->update([
            'video_feedback_status' => 'approved',
            'video_feedback_note' => 'Video đạt yêu cầu! Ban tổ chức sẽ liên hệ bạn về phần thưởng.',
        ]);
        $member->user->notify(new GenericNotification(
            '★', 'Video Feedback được duyệt! Ban tổ chức sẽ liên hệ bạn về phần thưởng.',
            route('challenge.show', $this->expedition->slug)
        ));
        $this->dispatch('toast', message: 'Đã duyệt video feedback!', type: 'success');
    }

    public function rejectVideoFeedback(int $memberId, string $note = ''): void
    {
        if (!Auth::check() || !Auth::user()->is_admin) return;
        $member = ExpeditionMember::findOrFail($memberId);
        $member->update([
            'video_feedback_status' => 'rejected',
            'video_feedback_note' => $note ?: 'Video chưa đạt yêu cầu. Hãy quay lại video chân thật và đầy cảm xúc hơn.',
            'video_feedback_url' => null, // allow resubmit
        ]);
        $member->user->notify(new GenericNotification(
            '✗', 'Video Feedback chưa đạt: ' . ($note ?: 'Hãy quay lại video chân thật hơn.'),
            route('challenge.show', $this->expedition->slug)
        ));
        $this->dispatch('toast', message: 'Đã từ chối video feedback', type: 'success');
    }

    // ─── Admin: update task video/SOP ────────────────────────
    public ?int $editingTaskId = null;
    public string $editTaskTitle = '';
    public string $editTaskDesc = '';
    public string $editTaskVideo = '';
    public string $editTaskMeetingAt = '';
    public string $editTaskSop = '';
    public string $editTaskEvidenceLabel = '';
    public string $editTaskAdminNote = '';
    public string $editTaskQuizJson = '';

    public function startEditTask(int $taskId): void
    {
        if (!Auth::check() || !Auth::user()->is_admin) return;
        $task = ChallengeTask::findOrFail($taskId);
        $this->editingTaskId = $taskId;
        $this->editTaskTitle = $task->title ?? '';
        $this->editTaskDesc = $task->description ?? '';
        $this->editTaskVideo = $task->video_url ?? '';
        $this->editTaskMeetingAt = $task->meeting_at ? $task->meeting_at->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d\TH:i') : '';
        $this->editTaskSop = $task->sop_content ?? '';
        $this->editTaskEvidenceLabel = $task->evidence_label ?? '';
        $this->editTaskAdminNote = $task->admin_note ?? '';
        $this->editTaskQuizJson = $task->quiz_json
            ? json_encode($task->quiz_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : '';
    }

    public function saveEditTask(): void
    {
        if (!Auth::check() || !Auth::user()->is_admin || !$this->editingTaskId) return;

        $quizJson = null;
        if (trim($this->editTaskQuizJson) !== '') {
            $decoded = json_decode($this->editTaskQuizJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->addError('editTaskQuizJson', 'JSON không hợp lệ: '.json_last_error_msg());
                return;
            }
            if (!is_array($decoded) || !array_is_list($decoded)) {
                $this->addError('editTaskQuizJson', 'Quiz phải là MẢNG (array) các câu hỏi.');
                return;
            }
            foreach ($decoded as $i => $q) {
                $missing = [];
                foreach (['q', 'options', 'correct'] as $key) {
                    if (!isset($q[$key])) $missing[] = $key;
                }
                if ($missing) {
                    $this->addError('editTaskQuizJson', "Câu ".($i+1).": thiếu field ".implode(', ', $missing));
                    return;
                }
                if (!is_array($q['options']) || !isset($q['options']['A'], $q['options']['B'], $q['options']['C'], $q['options']['D'])) {
                    $this->addError('editTaskQuizJson', "Câu ".($i+1).": options phải có đủ A/B/C/D");
                    return;
                }
                if (!in_array($q['correct'], ['A', 'B', 'C', 'D'], true)) {
                    $this->addError('editTaskQuizJson', "Câu ".($i+1).": correct phải là A/B/C/D");
                    return;
                }
            }
            $quizJson = $decoded;
        }

        ChallengeTask::where('id', $this->editingTaskId)->update([
            'title' => $this->editTaskTitle ?: 'Nhiệm vụ',
            'description' => $this->editTaskDesc ?: null,
            'video_url' => $this->editTaskVideo ?: null,
            'meeting_at' => $this->editTaskMeetingAt
                ? \Carbon\Carbon::parse($this->editTaskMeetingAt, 'Asia/Ho_Chi_Minh')->utc()
                : null,
            'sop_content' => $this->editTaskSop ?: null,
            'evidence_label' => $this->editTaskEvidenceLabel ?: null,
            'admin_note' => $this->editTaskAdminNote ?: null,
            'quiz_json' => $quizJson,
        ]);
        $this->editingTaskId = null;
        $this->editTaskVideo = '';
        $this->editTaskSop = '';
        $this->editTaskQuizJson = '';
        $this->expedition->refresh();
        $this->dispatch('toast', message: 'Đã cập nhật nhiệm vụ!', type: 'success');
    }

    public function cancelEditTask(): void
    {
        $this->editingTaskId = null;
    }

    // ─── Admin: review submissions ──────────────────────────
    public function approveAllPending(): void
    {
        if (!Auth::check() || !Auth::user()->is_admin) return;
        $taskIds = $this->expedition->tasks()->pluck('id');

        // Lấy pending rows TRƯỚC khi update — để biết XP target (user x task pairs chưa có approval)
        $pendingRows = \DB::table('challenge_task_completions')
            ->whereIn('challenge_task_id', $taskIds)
            ->where('status', 'pending')
            ->get(['id', 'user_id', 'challenge_task_id']);

        // Tìm các (user_id, task_id) đã có approval trước đó → các pair này KHÔNG award XP
        $existingApprovedPairs = \DB::table('challenge_task_completions')
            ->whereIn('challenge_task_id', $taskIds)
            ->where('status', 'approved')
            ->get(['user_id', 'challenge_task_id'])
            ->map(fn($r) => $r->user_id . ':' . $r->challenge_task_id)
            ->unique()
            ->flip();

        // Bulk update
        $pendingIds = $pendingRows->pluck('id');
        $count = \DB::table('challenge_task_completions')
            ->whereIn('id', $pendingIds)
            ->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

        if ($pendingIds->isNotEmpty()) {
            \DB::table('challenge_task_reviews')->insert(
                $pendingIds->map(fn($id) => [
                    'completion_id' => $id,
                    'reviewer_id' => Auth::id(),
                    'status' => 'approved',
                    'note' => null,
                    'created_at' => now(),
                ])->all()
            );
        }

        // Award XP: chỉ first approval per (user, task). Pair đã có approved trước đó → skip.
        $tasksById = ChallengeTask::whereIn('id', $taskIds)->get()->keyBy('id');
        $awardedPairs = [];
        foreach ($pendingRows as $row) {
            $key = $row->user_id . ':' . $row->challenge_task_id;
            if (isset($existingApprovedPairs[$key]) || isset($awardedPairs[$key])) continue;
            $awardedPairs[$key] = true;
            $user = \App\Models\User::find($row->user_id);
            $task = $tasksById->get($row->challenge_task_id);
            if ($user && $task) {
                app(XpService::class)->award(
                    $user, 'expedition_checkin', 1.0,
                    'Hoàn thành nhiệm vụ ngày ' . $task->day_number . ': ' . $task->title,
                    $this->expedition
                );
            }
        }

        // Notify all affected users
        $userIds = $pendingRows->pluck('user_id')->unique();
        \App\Models\User::whereIn('id', $userIds)->each(function ($user) {
            $user->notify(new GenericNotification(
                '✓', 'Bài nộp đã được duyệt!',
                route('challenge.show', $this->expedition->slug)
            ));
        });

        // Báo Telegram nếu ai đó vừa hoàn thành sạch đủ 21 ngày
        foreach ($userIds as $uid) {
            $this->notifyIfCompleted($uid);
        }

        $this->dispatch('toast', message: "Đã duyệt {$count} bài nộp!", type: 'success');
    }

    public function approveSubmission(int $completionId): void
    {
        if (!Auth::check() || !Auth::user()->is_admin) return;
        $completion = \DB::table('challenge_task_completions')->where('id', $completionId)->first();
        if (!$completion || $completion->status === 'approved') return;

        // First approval per (user, task) → award XP. Các approval sau (vd contest entries) KHÔNG award thêm.
        $hadPriorApproval = \DB::table('challenge_task_completions')
            ->where('challenge_task_id', $completion->challenge_task_id)
            ->where('user_id', $completion->user_id)
            ->where('status', 'approved')
            ->where('id', '!=', $completionId)
            ->exists();

        \DB::table('challenge_task_completions')->where('id', $completionId)->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        \DB::table('challenge_task_reviews')->insert([
            'completion_id' => $completionId,
            'reviewer_id' => Auth::id(),
            'status' => 'approved',
            'note' => null,
            'created_at' => now(),
        ]);

        $user = \App\Models\User::find($completion->user_id);
        if ($user) {
            if (!$hadPriorApproval) {
                $task = ChallengeTask::find($completion->challenge_task_id);
                if ($task) {
                    app(XpService::class)->award(
                        $user, 'expedition_checkin', 1.0,
                        'Hoàn thành nhiệm vụ ngày ' . $task->day_number . ': ' . $task->title,
                        $this->expedition
                    );
                }
            }
            $user->notify(new GenericNotification(
                '✓', 'Bài nộp đã được duyệt!',
                route('challenge.show', $this->expedition->slug)
            ));
        }

        $this->notifyIfCompleted($completion->user_id);

        $this->dispatch('toast', message: 'Đã duyệt bài nộp!', type: 'success');
    }

    public function rejectSubmission(int $completionId, string $note = ''): void
    {
        if (!Auth::check() || !Auth::user()->is_admin) return;
        $completion = \DB::table('challenge_task_completions')->where('id', $completionId)->first();
        if (!$completion) return;

        // Mini-game entry (row thứ 2+ của contest task) → không tăng reject_count, không phí 34k
        $task = ChallengeTask::find($completion->challenge_task_id);
        $isMiniGameEntry = false;
        if ($task && $task->is_contest) {
            $firstRow = \DB::table('challenge_task_completions')
                ->where('challenge_task_id', $completion->challenge_task_id)
                ->where('user_id', $completion->user_id)
                ->orderBy('created_at')
                ->first();
            $isMiniGameEntry = $firstRow && $firstRow->id !== $completion->id;
        }

        $rejectNote = $note ?: 'Bài nộp chưa đạt yêu cầu';

        $updates = [
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'review_note' => $rejectNote,
        ];
        if (!$isMiniGameEntry) {
            $updates['reject_count'] = \DB::raw('reject_count + 1');
        }

        \DB::table('challenge_task_completions')->where('id', $completionId)->update($updates);

        \DB::table('challenge_task_reviews')->insert([
            'completion_id' => $completionId,
            'reviewer_id' => Auth::id(),
            'status' => 'rejected',
            'note' => $rejectNote,
            'created_at' => now(),
        ]);

        // Notify user
        $user = \App\Models\User::find($completion->user_id);
        if ($user) {
            $user->notify(new GenericNotification(
                '✗',
                'Bài nộp bị từ chối: ' . $rejectNote . '. Vui lòng nộp lại.',
                route('challenge.show', $this->expedition->slug)
            ));
        }

        $this->dispatch('toast', message: 'Đã từ chối bài nộp', type: 'success');
    }

    // ─── Vote submissions (admin only) ───────────────────────
    // Hai vote type độc lập: 'good' (Vote hay) và 'excellent' (Vote xuất sắc).
    // Admin có thể toggle riêng từng loại trên cùng submission.
    public function toggleVote(int $completionId, string $type = 'good'): void
    {
        if (!Auth::check() || !Auth::user()->is_admin) return;
        if (!in_array($type, ['good', 'excellent'], true)) return;

        $row = \DB::table('submission_votes')
            ->where('completion_id', $completionId)
            ->where('user_id', Auth::id())
            ->where('vote_type', $type);

        if ($row->exists()) {
            $row->delete();
        } else {
            \DB::table('submission_votes')->insert([
                'completion_id' => $completionId,
                'user_id'       => Auth::id(),
                'vote_type'     => $type,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }

    // ─── Helpers ─────────────────────────────────────────────
    private function getApprovedMember(int $userId): ?ExpeditionMember
    {
        return $this->expedition->members()
            ->where('user_id', $userId)
            ->whereIn('status', ['approved', 'paid'])
            ->whereNull('kicked_at')
            ->first();
    }

    // ─── Báo Telegram + email chúc mừng khi member hoàn thành đủ tất cả các ngày ───
    // Hoàn thành = mọi ngày có bài chính đã DUYỆT (cho phép có ngày trễ; ngày trễ được
    // đánh dấu "(có trễ)" trong email). Chỉ chạy một lần / member (completion_notified_at).
    private function notifyIfCompleted(int $userId): void
    {
        $member = $this->expedition->members()
            ->where('user_id', $userId)
            ->whereIn('status', ['approved', 'paid'])
            ->whereNull('kicked_at')
            ->whereNull('completion_notified_at')
            ->first();
        if (!$member) return;

        $tasks = $this->expedition->tasks()
            ->orderBy('day_number')
            ->get(['id', 'day_number', 'title', 'label']);
        if ($tasks->isEmpty()) return;

        // Bài chính = row đầu tiên per task (theo created_at).
        $main = \DB::table('challenge_task_completions')
            ->whereIn('challenge_task_id', $tasks->pluck('id'))
            ->where('user_id', $userId)
            ->orderBy('created_at')
            ->get(['challenge_task_id', 'status', 'is_late'])
            ->groupBy('challenge_task_id')
            ->map(fn ($rows) => $rows->first());

        // Mọi ngày phải có bài chính đã duyệt; ngày trễ vẫn tính hoàn thành.
        $days = [];
        foreach ($tasks as $task) {
            $row = $main->get($task->id);
            if (!$row || $row->status !== 'approved') return;
            $days[] = [
                'day'     => (int) $task->day_number,
                'label'   => (string) ($task->title ?: $task->label ?: ('Ngày ' . $task->day_number)),
                'is_late' => (bool) $row->is_late,
            ];
        }

        $member->update(['completion_notified_at' => now()]);

        $u = $member->user;
        $lateCount = collect($days)->where('is_late', true)->count();

        // Email chúc mừng (không chặn flow nếu mail lỗi)
        if (!empty($u->email)) {
            try {
                \Illuminate\Support\Facades\Mail::to($u->email)->send(new \App\Mail\ChallengeCompletionMail(
                    userName:       $u->name,
                    challengeTitle: $this->expedition->title,
                    brandName:      config('app.name'),
                    days:           $days,
                    completedAt:    now()->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y'),
                    challengeUrl:   route('challenge.show', $this->expedition->slug),
                ));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $summary = $lateCount > 0 ? "(có {$lateCount} ngày trễ)" : "(không có ngày trễ)";
        \App\Services\TelegramService::sendCompletion(
            "🏆 <b>Hoàn thành " . $tasks->count() . " ngày</b>\n"
            . "Member {$u->name} ({$u->email}) đã hoàn thành thử thách {$summary}."
        );
    }

    // Day calculation + late check moved to Expedition model (freeze-aware)

    public function render()
    {
        $user = Auth::user();
        $myMember = $user
            ? $this->expedition->members()->where('user_id', $user->id)->first()
            : null;

        $isApproved = $myMember && in_array($myMember->status, ['approved', 'paid']) && !$myMember->kicked_at;
        $isPending = $myMember && $myMember->status === 'pending';
        $isPendingPayment = $myMember && $myMember->status === 'pending_payment';
        $currentDay = $isApproved ? $this->expedition->getCurrentDayForMember($myMember) : 0;

        $isAdminUser = Auth::check() && Auth::user()->is_admin;

        // Admin preview: nếu admin chưa join challenge, vẫn render full task UI như thành viên
        // Synthesize virtual member (in-memory only) để blade rendering không vỡ
        $isAdminPreview = false;
        if (!$isApproved && $isAdminUser) {
            $isAdminPreview = true;
            $requiredDays = $this->expedition->required_days ?? 21;
            $myMember = new ExpeditionMember([
                'expedition_id'           => $this->expedition->id,
                'user_id'                 => $user->id,
                'status'                  => 'approved',
                'class_at_join'           => $user->class ?? 'offer_architect',
                'joined_at'               => now(),
                'personal_starts_at'      => now()->subDays($requiredDays),
                'consecutive_missed_days' => 0,
            ]);
            $isApproved = true;
        }

        // Admin (dù là member thật hay đang preview) → unlock toàn bộ task để duyệt + check UX
        if ($isAdminUser) {
            $currentDay = $this->expedition->required_days;
        }

        // Reuse members/tasks đã eager load ở mount() thay vì re-query DB.
        $approvedMembers = $this->expedition->members
            ->whereIn('status', ['approved', 'paid'])
            ->whereNull('kicked_at')
            ->values();

        $pendingMembers = Auth::check() && Auth::user()->is_admin
            ? $this->expedition->members->where('status', 'pending')->values()
            : collect();

        $tasks = $this->expedition->tasks->sortBy('day_number')->values();

        // Task submission history: user sees own, admin sees all (paginated)
        $submissionsPaginator = null;
        $totalPendingSubmissions = 0;
        if ($tasks->count() > 0 && $user) {
            $taskIds = $tasks->pluck('id');

            if ($user->is_admin) {
                $totalPendingSubmissions = \DB::table('challenge_task_completions')
                    ->whereIn('challenge_task_id', $taskIds)
                    ->where('status', 'pending')
                    ->count();
            }

            $submissionsPaginator = \DB::table('challenge_task_completions')
                ->join('challenge_tasks', 'challenge_tasks.id', '=', 'challenge_task_completions.challenge_task_id')
                ->join('users', 'users.id', '=', 'challenge_task_completions.user_id')
                ->where('challenge_tasks.expedition_id', $this->expedition->id)
                ->when(!$user->is_admin, fn($q) => $q->where('challenge_task_completions.user_id', $user->id))
                ->when($this->submissionSearch && $user->is_admin, fn($q) => $q->where(function ($q2) {
                    $s = '%' . $this->submissionSearch . '%';
                    $q2->where('users.name', 'ilike', $s)
                       ->orWhere('users.email', 'ilike', $s)
                       ->orWhere('users.username', 'ilike', $s)
                       ->orWhere('challenge_task_completions.evidence', 'ilike', $s);
                }))
                ->leftJoin('submission_votes', 'submission_votes.completion_id', '=', 'challenge_task_completions.id')
                ->selectRaw("challenge_task_completions.id as completion_id,
                    users.name, users.username, users.email, users.id as user_id, users.avatar,
                    challenge_tasks.day_number, challenge_tasks.title as task_title,
                    challenge_task_completions.evidence, challenge_task_completions.is_late,
                    challenge_task_completions.status as review_status,
                    challenge_task_completions.review_note, challenge_task_completions.created_at,
                    challenge_task_completions.updated_at,
                    COUNT(CASE WHEN submission_votes.vote_type = 'good' THEN 1 END) as good_vote_count,
                    COUNT(CASE WHEN submission_votes.vote_type = 'excellent' THEN 1 END) as excellent_vote_count,
                    COUNT(submission_votes.id) as vote_count")
                ->groupBy(
                    'challenge_task_completions.id', 'users.name', 'users.username', 'users.email',
                    'users.id', 'users.avatar', 'challenge_tasks.day_number', 'challenge_tasks.title',
                    'challenge_task_completions.evidence', 'challenge_task_completions.is_late',
                    'challenge_task_completions.status', 'challenge_task_completions.review_note',
                    'challenge_task_completions.created_at', 'challenge_task_completions.updated_at'
                )
                ->orderByRaw("CASE challenge_task_completions.status WHEN 'pending' THEN 0 ELSE 1 END")
                ->orderByDesc('challenge_task_completions.created_at')
                ->paginate(self::PER_PAGE, ['*'], 'submissionPage', $this->submissionPage);
        }
        // User's voted submission IDs + review history for visible submissions
        // $myVotedIds is now a per-type map: ['good' => [ids...], 'excellent' => [ids...]]
        $myVotedIds = ['good' => [], 'excellent' => []];
        $reviewHistory = collect();
        if ($user && $submissionsPaginator) {
            $completionIds = collect($submissionsPaginator->items())->pluck('completion_id')->toArray();
            if ($completionIds) {
                $myVoteRows = \DB::table('submission_votes')
                    ->where('user_id', $user->id)
                    ->whereIn('completion_id', $completionIds)
                    ->select('completion_id', 'vote_type')
                    ->get();
                foreach ($myVoteRows as $r) {
                    $myVotedIds[$r->vote_type][] = (int) $r->completion_id;
                }

                $reviewHistory = \DB::table('challenge_task_reviews')
                    ->whereIn('completion_id', $completionIds)
                    ->orderBy('created_at')
                    ->get(['completion_id', 'status', 'note', 'created_at'])
                    ->groupBy('completion_id');
            }
        }

        $completedTaskIds = [];
        $rejectedTaskIds = [];
        $completedTaskCount = 0;
        $myCompletions = collect(); // user's own submissions keyed by task_id (1 row per task; for contest tasks lấy row đầu)
        $myAllCompletions = collect(); // tất cả rows per task (contest tasks có nhiều row)
        if ($user && $tasks->count() > 0) {
            $allRows = \DB::table('challenge_task_completions')
                ->where('user_id', $user->id)
                ->whereIn('challenge_task_id', $tasks->pluck('id'))
                ->orderBy('created_at')
                ->get();
            $myAllCompletions = $allRows->groupBy('challenge_task_id');
            // $myCompletions: row đầu tiên (chronological) per task = "bài chính" theo SOP
            $myCompletions = $myAllCompletions->map(fn ($rows) => $rows->first());
            $completedTaskIds = $myCompletions->keys()->toArray();
            $rejectedTaskIds = $myCompletions
                ->where('status', 'rejected')
                ->keys()
                ->toArray();
            $pendingTaskIds = $myCompletions
                ->where('status', 'pending')
                ->keys()
                ->toArray();
            $completedTaskCount = count($completedTaskIds) - count($rejectedTaskIds) - count($pendingTaskIds);
        }

        $personalDaysLeft = null;
        if ($isApproved && $myMember->personal_starts_at) {
            $adjustedHours = $this->expedition->getAdjustedHoursForMember($myMember);
            $totalHoursNeeded = $this->expedition->required_days * 24;
            $personalDaysLeft = max(0, (int) ceil(($totalHoursNeeded - $adjustedHours) / 24));
        }

        // Admin: member progress report
        $memberReport = collect();
        if (Auth::check() && Auth::user()->is_admin && $tasks->count() > 0 && $approvedMembers->count() > 0) {
            $allCompletions = \DB::table('challenge_task_completions')
                ->whereIn('challenge_task_id', $tasks->pluck('id'))
                ->orderBy('created_at')
                ->get()
                ->groupBy('user_id');

            $dayByTask = $tasks->keyBy('id')->map(fn($t) => (int) $t->day_number);

            $memberReport = $approvedMembers
                ->filter(fn($m) => $m->personal_starts_at !== null)
                ->map(function ($member) use ($tasks, $allCompletions, $dayByTask) {
                $memberDay = $this->expedition->getCurrentDayForMember($member);
                $completed = $allCompletions->get($member->user_id, collect());
                // Bài chính = row đầu tiên per task (theo created_at). Các row sau = mini-game entries, KHÔNG đếm vô báo cáo.
                $mainSubs = $completed->groupBy('challenge_task_id')->map(fn($rows) => $rows->first());
                $completedCount = $mainSubs->count();
                $lateCount = $mainSubs->where('is_late', true)->count();
                $rejectedCount = $mainSubs->where('status', 'rejected')->count();

                $validCount = $completedCount - $rejectedCount;
                $expiredDays = $this->expedition->getExpiredDaysForMember($member);
                $missedCount = max(0, $expiredDays - $validCount);

                // KPI mới: Miss = ngày đã qua deadline (1..K-1) mà chưa có bài duyệt.
                // Day đang trong giờ (Day K) còn hạn nên không tính Miss; nhưng nếu đã duyệt rồi thì tính vào "có trễ".
                $approvedDayLateMap = [];
                foreach ($completed as $row) {
                    if ($row->status !== 'approved') continue;
                    $day = $dayByTask[$row->challenge_task_id] ?? null;
                    if ($day === null) continue;
                    $approvedDayLateMap[$day] = ($approvedDayLateMap[$day] ?? false) || (bool) $row->is_late;
                }
                $missingPastDeadline = 0;
                for ($d = 1; $d < $memberDay; $d++) {
                    if (!array_key_exists($d, $approvedDayLateMap)) $missingPastDeadline++;
                }
                $lateInApproved = false;
                foreach ($approvedDayLateMap as $d => $wasLate) {
                    if ($d <= $memberDay && $wasLate) { $lateInApproved = true; break; }
                }

                return (object) [
                    'member' => $member,
                    'current_day' => $memberDay,
                    'completed' => $validCount,
                    'rejected' => $rejectedCount,
                    'late' => $lateCount,
                    'missed' => $missedCount,
                    'total' => $tasks->count(),
                    'pct' => $tasks->count() > 0 ? round($validCount / $tasks->count() * 100) : 0,
                    'missing_past_deadline' => $missingPastDeadline,
                    'late_in_approved' => $lateInApproved,
                ];
            })->sortBy([
                ['missed', 'asc'],       // ít miss trước
                ['rejected', 'asc'],     // ít bị reject trước
                ['completed', 'desc'],   // hoàn thành nhiều trước
            ])->values();
        }

        // Filter member report by search
        if ($this->reportSearch) {
            $s = mb_strtolower($this->reportSearch);
            $memberReport = $memberReport->filter(
                fn($r) => str_contains(mb_strtolower($r->member->user->name), $s)
            )->values();
        }

        // Summary stats for member report dashboard (computed before search filter)
        $reportStats = null;
        if ($memberReport->count() > 0) {
            $reportStats = (object) [
                'total'      => $memberReport->count(),
                'on_track'      => $memberReport->filter(fn($r) => $r->missing_past_deadline === 0 && !$r->late_in_approved && $r->completed < $r->total)->count(),
                'on_track_late' => $memberReport->filter(fn($r) => $r->missing_past_deadline === 0 &&  $r->late_in_approved)->count(),
                'completed'     => $memberReport->filter(fn($r) => $r->completed === $r->total && !$r->late_in_approved)->count(),
                'miss'          => $memberReport->filter(fn($r) => $r->missing_past_deadline > 0)->count(),
                'submissions_total'   => $totalPendingSubmissions + \DB::table('challenge_task_completions')
                    ->whereIn('challenge_task_id', $tasks->pluck('id'))
                    ->whereIn('status', ['approved', 'rejected'])
                    ->count(),
                'submissions_pending' => $totalPendingSubmissions,
                'submissions_approved' => \DB::table('challenge_task_completions')
                    ->whereIn('challenge_task_id', $tasks->pluck('id'))
                    ->where('status', 'approved')->count(),
                'submissions_rejected' => \DB::table('challenge_task_completions')
                    ->whereIn('challenge_task_id', $tasks->pluck('id'))
                    ->where('status', 'rejected')->count(),
            ];
        }

        // Paginate member report (after search filter)
        $reportTotal = $memberReport->count();
        $reportLastPage = max(1, (int) ceil($reportTotal / self::PER_PAGE));
        $this->reportPage = min($this->reportPage, $reportLastPage);
        $memberReportPage = $memberReport->forPage($this->reportPage, self::PER_PAGE);

        // Task deadlines for current user (freeze-aware, per-member anchor rounding)
        $taskDeadlines = [];
        $contestEnds = [];
        if ($isApproved && $myMember->personal_starts_at) {
            foreach ($tasks as $task) {
                $taskDeadlines[$task->id] = $this->expedition->getDeadlineForMemberAtDay($myMember, $task->day_number);
                if ($task->is_contest && $task->contest_duration_hours) {
                    $contestEnds[$task->id] = $this->expedition->getContestEndForMemberAtDay($myMember, $task->day_number);
                }
            }
        }

        // Unlock time for the immediate next day only
        $nextDayUnlockAt = null;
        if ($myMember?->personal_starts_at && $currentDay >= 1 && $currentDay < $this->expedition->required_days) {
            $nextDayUnlockAt = $this->expedition->getDeadlineForMemberAtDay($myMember, $currentDay);
        }

        // Late status for completed tasks (reuse $myCompletions, avoid extra query)
        $lateTaskIds = $myCompletions->where('is_late', true)->keys()->toArray();

        return view('livewire.challenge-detail', [
            'approvedMembers' => $approvedMembers,
            'pendingMembers' => $pendingMembers,
            'submissions' => $submissionsPaginator,
            'totalPendingSubmissions' => $totalPendingSubmissions,
            'isApproved' => $isApproved,
            'isPending' => $isPending,
            'isPendingPayment' => $isPendingPayment,
            'isWebhookMember' => (bool) $user?->isWebhookCreated(),
            'isAdminPreview' => $isAdminPreview,
            'myMember' => $myMember,
            'tasks' => $tasks,
            'completedTaskIds' => $completedTaskIds,
            'rejectedTaskIds' => $rejectedTaskIds,
            'pendingTaskIds' => $pendingTaskIds ?? [],
            'completedTaskCount' => $completedTaskCount,
            'myCompletions' => $myCompletions,
            'myAllCompletions' => $myAllCompletions,
            'currentDay' => $currentDay,
            'personalDaysLeft' => $personalDaysLeft,
            'memberReport' => $memberReportPage,
            'reportStats' => $reportStats,
            'reportTotal' => $reportTotal,
            'reportLastPage' => $reportLastPage,
            'taskDeadlines' => $taskDeadlines,
            'contestEnds' => $contestEnds,
            'lateTaskIds' => $lateTaskIds,
            'nextDayUnlockAt' => $nextDayUnlockAt,
            'myVotedIds' => $myVotedIds,
            'reviewHistory' => $reviewHistory,
            'premiumLocked' => $this->premiumLocked,
        ])->layout('layouts.app', ['title' => $this->expedition->title . ' — Challenge']);
    }
}
