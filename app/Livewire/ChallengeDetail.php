<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\ChallengeTask;
use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\User;
use App\Notifications\GenericNotification;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Learning\Application\ChallengeAccessService;
use Modules\Learning\Application\ChallengeCheckinOutcome;
use Modules\Learning\Application\ChallengeCheckinService;
use Modules\Learning\Application\ChallengeEnrollmentService;
use Modules\Learning\Application\ChallengeSubmissionOutcome;
use Modules\Learning\Application\ChallengeSubmissionService;
use Modules\Learning\Application\ChallengeTaskManagementService;
use Modules\Learning\Application\ChallengeVideoFeedbackOutcome;
use Modules\Learning\Application\ChallengeVideoFeedbackService;
use Modules\Learning\Application\SubmissionReviewService;
use Modules\Learning\Application\SubmissionVoteService;

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

    public function updatedSubmissionSearch(): void
    {
        $this->submissionPage = 1;
    }

    public function updatedReportSearch(): void
    {
        $this->reportPage = 1;
    }

    public function mount(string $slug): void
    {
        $this->expedition = app(ChallengeAccessService::class)->find($slug);
        $this->expedition->load(['leader', 'members.user', 'tasks']);
        $user = $this->currentUser();
        $this->premiumLocked = app(ChallengeAccessService::class)->isPremiumLocked($this->expedition, $user);
    }

    // ─── Enrollment ─────────────────────────────────────────
    public function requestJoin(): void
    {
        if (! Auth::check()) {
            return;
        }
        $user = $this->currentUser();

        if ($this->premiumLocked) {
            $this->dispatch('toast', message: 'Challenge này thuộc Premium. Hãy nâng hạng membership để tham gia.', type: 'info');

            return;
        }

        $outcome = app(ChallengeEnrollmentService::class)->request($this->expedition, $user);

        if ($outcome === ChallengeEnrollmentService::DUPLICATE) {
            $this->dispatch('toast', message: 'Bạn đã đăng ký Challenge này rồi', type: 'error');

            return;
        }

        if ($outcome === ChallengeEnrollmentService::AUTO_APPROVED) {
            $this->dispatch('toast', message: 'Đã tham gia Challenge! Bấm "Bắt đầu" khi bạn sẵn sàng.', type: 'success');
            $this->expedition->refresh();

            return;
        }

        if ($outcome === ChallengeEnrollmentService::PENDING_PAYMENT) {
            $this->dispatch('toast', message: 'Quét mã QR để chuyển khoản. Hệ thống tự duyệt ngay khi nhận được tiền.', type: 'success');
            $this->expedition->refresh();

            return;
        }

        $this->dispatch('toast', message: 'Đã gửi yêu cầu tham gia! Vui lòng chờ Admin duyệt.', type: 'success');
        $this->expedition->refresh();
    }

    public function cancelRequest(): void
    {
        if (! Auth::check()) {
            return;
        }
        if (! app(ChallengeEnrollmentService::class)->cancel($this->expedition, $this->currentUser())) {
            return;
        }
        $this->dispatch('toast', message: 'Đã rút yêu cầu tham gia', type: 'success');
        $this->expedition->refresh();
    }

    public function approveRequest(int $memberId): void
    {
        if (! Auth::check() || ! $this->currentUser()->isBrandAdmin()) {
            return;
        }

        $member = app(ChallengeEnrollmentService::class)->approve(
            $this->expedition,
            $memberId,
            $this->currentUser(),
        );
        if (! $member) {
            return;
        }

        $this->dispatch('toast', message: 'Đã duyệt '.$member->user->name, type: 'success');
        $this->expedition->refresh();
    }

    public function rejectRequest(int $memberId): void
    {
        if (! Auth::check() || ! $this->currentUser()->isBrandAdmin()) {
            return;
        }

        $member = app(ChallengeEnrollmentService::class)->reject(
            $this->expedition,
            $memberId,
            $this->currentUser(),
        );
        if (! $member) {
            return;
        }

        $this->dispatch('toast', message: 'Đã từ chối '.$member->user->name, type: 'success');
        $this->expedition->refresh();
    }

    // ─── Start challenge (user clicks after approval) ──────
    public function startMyChallenge(): void
    {
        if (! Auth::check()) {
            return;
        }
        if (! app(ChallengeEnrollmentService::class)->start($this->expedition, $this->currentUser())) {
            return;
        }

        $this->dispatch('toast', message: 'Challenge đã bắt đầu! Chúc bạn chinh phục thành công!', type: 'success');
        $this->expedition->refresh();
    }

    // ─── Check-in ───────────────────────────────────────────
    public function checkin(): void
    {
        if (! Auth::check()) {
            return;
        }
        $this->validate();

        $user = $this->currentUser();
        $outcome = app(ChallengeCheckinService::class)->checkin(
            $this->expedition,
            $user,
            $this->checkinContent,
        );
        if ($outcome === ChallengeCheckinOutcome::NotEnrolled) {
            $this->addError('checkinContent', 'Bạn chưa được duyệt tham gia Challenge này.');

            return;
        }
        if ($outcome === ChallengeCheckinOutcome::AlreadyCheckedIn) {
            $this->addError('checkinContent', 'Bạn đã check-in hôm nay rồi.');

            return;
        }

        $this->reset('checkinContent');
        $this->expedition->refresh();
    }

    // ─── Tasks ──────────────────────────────────────────────
    /** @var array<int, string> */
    public array $taskEvidence = [];

    /** @var array<int, array<string, mixed>> */
    public array $structuredSubmission = [];

    public function completeTask(int $taskId): void
    {
        if (! Auth::check()) {
            return;
        }
        $user = $this->currentUser();

        $task = ChallengeTask::where('id', $taskId)
            ->where('expedition_id', $this->expedition->id)
            ->firstOrFail();

        $evidence = $this->taskEvidence[$taskId] ?? '';
        if (blank($evidence)) {
            $this->dispatch('toast', message: 'Vui lòng cung cấp bằng chứng hoàn thành!', type: 'error');

            return;
        }

        if (! $this->validateStructuredSubmission($task)) {
            return;
        }
        $submissionPayload = $this->buildSubmissionPayload($task);
        $result = app(ChallengeSubmissionService::class)->submit(
            $this->expedition,
            $taskId,
            $user,
            $evidence,
            $submissionPayload,
        );
        if ($result->outcome !== ChallengeSubmissionOutcome::Submitted) {
            $this->showSubmissionOutcome($result->outcome);

            return;
        }
        $late = $result->isLate;

        // XP cộng khi admin approve, KHÔNG cộng khi submit (xem approveSubmission/approveAllPending)

        $this->taskEvidence[$taskId] = '';
        $msg = 'Đã nộp bài ngày '.$task->day_number.' — chờ admin duyệt.';
        if ($late) {
            $msg .= ' (Nộp trễ)';
        }
        $this->dispatch('toast', message: $msg, type: $late ? 'warning' : 'success');

    }

    // ─── Submit mini-game ứng dụng (contest task only — 1 row/user) ───
    public function submitMiniGame(int $taskId): void
    {
        if (! Auth::check()) {
            return;
        }
        $user = $this->currentUser();

        $evidence = $this->taskEvidence[$taskId] ?? '';
        if (blank($evidence)) {
            $this->dispatch('toast', message: 'Vui lòng cung cấp bằng chứng ứng dụng.', type: 'error');

            return;
        }

        $result = app(ChallengeSubmissionService::class)->submitContestEntry(
            $this->expedition,
            $taskId,
            $user,
            $evidence,
        );
        if ($result->outcome !== ChallengeSubmissionOutcome::ContestSubmitted) {
            $this->showContestSubmissionOutcome($result->outcome);

            return;
        }
        $task = $result->task;
        if (! $task) {
            return;
        }
        $late = $result->isLate;

        $this->taskEvidence[$taskId] = '';
        $this->dispatch('toast', message: 'Đã nộp ứng dụng. Chờ admin duyệt.', type: 'success');

    }

    // ─── Resubmit rejected task ───────────────────────────────
    public function resubmitTask(int $taskId): void
    {
        if (! Auth::check()) {
            return;
        }
        $user = $this->currentUser();

        $task = ChallengeTask::where('id', $taskId)
            ->where('expedition_id', $this->expedition->id)
            ->firstOrFail();

        $evidence = $this->taskEvidence[$taskId] ?? '';
        if (blank($evidence)) {
            $this->dispatch('toast', message: 'Vui lòng cung cấp bằng chứng mới!', type: 'error');

            return;
        }

        if (! $this->validateStructuredSubmission($task)) {
            return;
        }
        $submissionPayload = $this->buildSubmissionPayload($task);
        $result = app(ChallengeSubmissionService::class)->resubmit(
            $this->expedition,
            $taskId,
            $user,
            $evidence,
            $submissionPayload,
        );
        if ($result->outcome !== ChallengeSubmissionOutcome::Resubmitted) {
            $this->showSubmissionOutcome($result->outcome, true);

            return;
        }

        $this->taskEvidence[$taskId] = '';
        $this->dispatch('toast', message: 'Đã nộp lại bài! Chờ admin duyệt.', type: 'success');
    }

    // ─── Video feedback ────────────────────────────────────────
    private function validateStructuredSubmission(ChallengeTask $task): bool
    {
        // The evidence textarea is the single required submission field. The
        // curriculum intentionally keeps proof lightweight: image/link first,
        // with no mandatory three-part reflection on every day.
        return true;
    }

    /** @return array<string, mixed>|null */
    private function buildSubmissionPayload(ChallengeTask $task): ?array
    {
        if (empty($task->instruction_payload)) {
            return null;
        }

        $fields = $this->structuredSubmission[$task->id] ?? [];

        return [
            'checklist' => array_values(array_filter($fields['checklist'] ?? [], fn ($value) => (bool) $value)),
            'reflection' => [
                'ai_request' => trim((string) ($fields['reflection_request'] ?? '')),
                'ai_files' => trim((string) ($fields['reflection_files'] ?? '')),
                'verification' => trim((string) ($fields['reflection_verification'] ?? '')),
            ],
            'submitted_at' => now()->toIso8601String(),
        ];
    }

    private function showSubmissionOutcome(ChallengeSubmissionOutcome $outcome, bool $isResubmission = false): void
    {
        if ($outcome === ChallengeSubmissionOutcome::Frozen && $this->expedition->freeze_ends_at) {
            $this->dispatch(
                'toast',
                message: 'Nhiệm vụ tạm dừng trong kỳ nghỉ. Tiếp tục vào '.$this->expedition->freeze_ends_at->timezone('Asia/Ho_Chi_Minh')->format('d/m'),
                type: 'warning',
            );

            return;
        }
        $messages = [
            ChallengeSubmissionOutcome::TaskLocked->value => ['Nhiệm vụ đang tạm khóa, vui lòng quay lại sau.', 'warning'],
            ChallengeSubmissionOutcome::NotEnrolled->value => ['Bạn chưa được duyệt tham gia Challenge này.', 'error'],
            ChallengeSubmissionOutcome::NotUnlocked->value => ['Nhiệm vụ này chưa mở. Hãy hoàn thành các ngày trước trước.', 'warning'],
            ChallengeSubmissionOutcome::AlreadySubmitted->value => ['Bạn đã nộp bài cho nhiệm vụ này.', 'info'],
            ChallengeSubmissionOutcome::NotRejected->value => ['Không có bài bị từ chối để nộp lại.', 'info'],
            ChallengeSubmissionOutcome::MissingEvidence->value => [
                $isResubmission ? 'Vui lòng cung cấp bằng chứng mới!' : 'Vui lòng cung cấp bằng chứng hoàn thành!',
                'error',
            ],
        ];
        [$message, $type] = $messages[$outcome->value] ?? [null, null];
        if ($message && $type) {
            $this->dispatch('toast', message: $message, type: $type);
        }
    }

    private function showContestSubmissionOutcome(ChallengeSubmissionOutcome $outcome): void
    {
        if (in_array($outcome, [
            ChallengeSubmissionOutcome::Frozen,
            ChallengeSubmissionOutcome::TaskLocked,
            ChallengeSubmissionOutcome::NotEnrolled,
            ChallengeSubmissionOutcome::NotUnlocked,
        ], true)) {
            $this->showSubmissionOutcome($outcome);

            return;
        }

        $messages = [
            ChallengeSubmissionOutcome::SubmissionClosed->value => 'Mini-game đã hết hạn — không nộp ứng dụng được nữa.',
            ChallengeSubmissionOutcome::MainSubmissionMissing->value => 'Hãy nộp bài chính ngày 15 trước.',
            ChallengeSubmissionOutcome::MainSubmissionPending->value => 'Bài chính đang chờ admin duyệt — đợi được duyệt mới tham gia mini-game được.',
            ChallengeSubmissionOutcome::MainSubmissionRejected->value => 'Bài chính đang bị từ chối — chỉnh và nộp lại bài chính trước.',
            ChallengeSubmissionOutcome::ContestEntryPending->value => 'Ứng dụng trước đang chờ duyệt — vui lòng đợi rồi mới nộp tiếp.',
            ChallengeSubmissionOutcome::MissingEvidence->value => 'Vui lòng cung cấp bằng chứng ứng dụng.',
        ];
        $message = $messages[$outcome->value] ?? null;
        if ($message) {
            $this->dispatch('toast', message: $message, type: 'error');
        }
    }

    public string $videoFeedbackUrl = '';

    public function submitVideoFeedback(): void
    {
        if (! Auth::check()) {
            return;
        }
        $outcome = app(ChallengeVideoFeedbackService::class)->submit(
            $this->expedition,
            $this->currentUser(),
            $this->videoFeedbackUrl,
        );
        if ($outcome === ChallengeVideoFeedbackOutcome::MissingUrl) {
            $this->dispatch('toast', message: 'Vui lòng paste link video!', type: 'error');

            return;
        }
        if ($outcome === ChallengeVideoFeedbackOutcome::NotEnrolled) {
            return;
        }

        $this->videoFeedbackUrl = '';
        $this->dispatch('toast', message: 'Đã gửi Video Feedback! Chờ admin duyệt.', type: 'success');
        $this->expedition->refresh();
    }

    public function approveVideoFeedback(int $memberId): void
    {
        if (! Auth::check() || ! $this->currentUser()->isBrandAdmin()) {
            return;
        }
        $member = app(ChallengeVideoFeedbackService::class)->approve(
            $this->expedition,
            $memberId,
            $this->currentUser(),
        );
        if (! $member) {
            return;
        }
        $this->dispatch('toast', message: 'Đã duyệt video feedback!', type: 'success');
    }

    public function rejectVideoFeedback(int $memberId, string $note = ''): void
    {
        if (! Auth::check() || ! $this->currentUser()->isBrandAdmin()) {
            return;
        }
        $rejectNote = $note ?: 'Video chưa đạt yêu cầu. Hãy quay lại video chân thật và đầy cảm xúc hơn.';
        $member = app(ChallengeVideoFeedbackService::class)->reject(
            $this->expedition,
            $memberId,
            $this->currentUser(),
            $rejectNote,
        );
        if (! $member) {
            return;
        }
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
        if (! Auth::check() || ! $this->currentUser()->isBrandAdmin()) {
            return;
        }
        $task = app(ChallengeTaskManagementService::class)->find($taskId, $this->currentUser());
        if (! $task || $task->expedition_id !== $this->expedition->id) {
            return;
        }
        $this->editingTaskId = $taskId;
        $this->editTaskTitle = $task->title ?? '';
        $this->editTaskDesc = $task->description ?? '';
        $this->editTaskVideo = $task->video_url ?? '';
        $this->editTaskMeetingAt = $task->meeting_at ? $task->meeting_at->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d\TH:i') : '';
        $this->editTaskSop = $task->sop_content ?? '';
        $this->editTaskEvidenceLabel = $task->evidence_label ?? '';
        $this->editTaskAdminNote = $task->admin_note ?? '';
        $this->editTaskQuizJson = $task->quiz_json
            ? (string) json_encode($task->quiz_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : '';
    }

    public function saveEditTask(): void
    {
        if (! Auth::check() || ! $this->currentUser()->isBrandAdmin() || ! $this->editingTaskId) {
            return;
        }

        $quizJson = null;
        if (trim($this->editTaskQuizJson) !== '') {
            $decoded = json_decode($this->editTaskQuizJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->addError('editTaskQuizJson', 'JSON không hợp lệ: '.json_last_error_msg());

                return;
            }
            if (! is_array($decoded) || ! array_is_list($decoded)) {
                $this->addError('editTaskQuizJson', 'Quiz phải là MẢNG (array) các câu hỏi.');

                return;
            }
            foreach ($decoded as $i => $q) {
                $missing = [];
                foreach (['q', 'options', 'correct'] as $key) {
                    if (! isset($q[$key])) {
                        $missing[] = $key;
                    }
                }
                if ($missing) {
                    $this->addError('editTaskQuizJson', 'Câu '.($i + 1).': thiếu field '.implode(', ', $missing));

                    return;
                }
                if (! is_array($q['options']) || ! isset($q['options']['A'], $q['options']['B'], $q['options']['C'], $q['options']['D'])) {
                    $this->addError('editTaskQuizJson', 'Câu '.($i + 1).': options phải có đủ A/B/C/D');

                    return;
                }
                if (! in_array($q['correct'], ['A', 'B', 'C', 'D'], true)) {
                    $this->addError('editTaskQuizJson', 'Câu '.($i + 1).': correct phải là A/B/C/D');

                    return;
                }
            }
            $quizJson = $decoded;
        }

        $task = app(ChallengeTaskManagementService::class)->save(
            $this->expedition->id,
            $this->editingTaskId,
            $this->currentUser(),
            [
                'title' => $this->editTaskTitle ?: 'Nhiệm vụ',
                'description' => $this->editTaskDesc ?: null,
                'video_url' => $this->editTaskVideo ?: null,
                'meeting_at' => $this->editTaskMeetingAt
                    ? Carbon::parse($this->editTaskMeetingAt, 'Asia/Ho_Chi_Minh')->utc()
                    : null,
                'sop_content' => $this->editTaskSop ?: null,
                'evidence_label' => $this->editTaskEvidenceLabel ?: null,
                'admin_note' => $this->editTaskAdminNote ?: null,
                'quiz_json' => $quizJson,
            ],
        );
        if (! $task) {
            return;
        }
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
    /** @var array<int, int|string> */
    public array $reviewScores = [];

    /** @var array<int, array<string, mixed>> */
    public array $reviewRubrics = [];

    public function approveAllPending(): void
    {
        if (! Auth::check() || ! $this->currentUser()->isBrandAdmin()) {
            return;
        }
        $result = app(SubmissionReviewService::class)->approveAllPending(
            $this->expedition,
            $this->currentUser(),
        );
        if (! $result) {
            return;
        }
        $this->dispatch('toast', message: 'Đã duyệt '.$result->completions->count().' bài nộp!', type: 'success');
    }

    public function approveSubmission(int $completionId): void
    {
        if (! Auth::check() || ! $this->currentUser()->isBrandAdmin()) {
            return;
        }
        $score = isset($this->reviewScores[$completionId]) && $this->reviewScores[$completionId] !== ''
            ? (int) $this->reviewScores[$completionId]
            : null;
        $rubric = $this->reviewRubrics[$completionId] ?? null;
        $result = app(SubmissionReviewService::class)->approve(
            $this->expedition,
            $completionId,
            $this->currentUser(),
            $score,
            $rubric,
        );
        if (! $result) {
            return;
        }
        $this->dispatch('toast', message: 'Đã duyệt bài nộp!', type: 'success');
    }

    public function rejectSubmission(int $completionId, string $note = ''): void
    {
        if (! Auth::check() || ! $this->currentUser()->isBrandAdmin()) {
            return;
        }
        $score = isset($this->reviewScores[$completionId]) && $this->reviewScores[$completionId] !== ''
            ? (int) $this->reviewScores[$completionId]
            : null;
        $rubric = $this->reviewRubrics[$completionId] ?? null;

        // Mini-game entry (row thứ 2+ của contest task) → không tăng reject_count, không phí 34k
        $rejectNote = $note ?: 'Bài nộp chưa đạt yêu cầu';

        $result = app(SubmissionReviewService::class)->reject(
            $this->expedition,
            $completionId,
            $this->currentUser(),
            $rejectNote,
            $score,
            $rubric,
        );
        if (! $result) {
            return;
        }
        $this->dispatch('toast', message: 'Đã từ chối bài nộp', type: 'success');
    }

    // ─── Vote submissions (admin only) ───────────────────────
    // Hai vote type độc lập: 'good' (Vote hay) và 'excellent' (Vote xuất sắc).
    // Admin có thể toggle riêng từng loại trên cùng submission.
    public function toggleVote(int $completionId, string $type = 'good'): void
    {
        if (! Auth::check() || ! $this->currentUser()->isBrandAdmin()) {
            return;
        }
        app(SubmissionVoteService::class)->toggle(
            $this->expedition,
            $completionId,
            $this->currentUser(),
            $type,
        );
    }

    private function currentUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        return $user;
    }

    public function render(): View
    {
        $user = Auth::user();
        $myMember = $user
            ? $this->expedition->members()->where('user_id', $user->id)->first()
            : null;

        $isApproved = $myMember && in_array($myMember->status, ['approved', 'paid']) && ! $myMember->kicked_at;
        $isPending = $myMember && $myMember->status === 'pending';
        $isPendingPayment = $myMember && $myMember->status === 'pending_payment';
        $currentDay = $isApproved ? $this->expedition->getCurrentDayForMember($myMember) : 0;

        $isAdminUser = Auth::check() && $this->currentUser()->isBrandAdmin();

        // Admin preview: nếu admin chưa join challenge, vẫn render full task UI như thành viên
        // Synthesize virtual member (in-memory only) để blade rendering không vỡ
        $isAdminPreview = false;
        if (! $isApproved && $isAdminUser) {
            $isAdminPreview = true;
            $requiredDays = $this->expedition->required_days ?? 21;
            $myMember = new ExpeditionMember([
                'expedition_id' => $this->expedition->id,
                'user_id' => $this->currentUser()->id,
                'status' => 'approved',
                'class_at_join' => $this->currentUser()->class ?? 'offer_architect',
                'joined_at' => now(),
                'personal_starts_at' => now()->subDays($requiredDays),
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

        $pendingMembers = Auth::check() && $this->currentUser()->isBrandAdmin()
            ? $this->expedition->members->where('status', 'pending')->values()
            : collect();

        $tasks = $this->expedition->tasks->sortBy('day_number')->values();

        // Task submission history: user sees own, admin sees all (paginated)
        $submissionsPaginator = null;
        $totalPendingSubmissions = 0;
        if ($tasks->count() > 0 && $user) {
            $taskIds = $tasks->pluck('id');

            if ($user->isBrandAdmin()) {
                $totalPendingSubmissions = \DB::table('challenge_task_completions')
                    ->whereIn('challenge_task_id', $taskIds)
                    ->where('status', 'pending')
                    ->count();
            }

            $submissionsPaginator = \DB::table('challenge_task_completions')
                ->join('challenge_tasks', 'challenge_tasks.id', '=', 'challenge_task_completions.challenge_task_id')
                ->join('users', 'users.id', '=', 'challenge_task_completions.user_id')
                ->where('challenge_tasks.expedition_id', $this->expedition->id)
                ->when(! $user->isBrandAdmin(), fn ($q) => $q->where('challenge_task_completions.user_id', $user->id))
                ->when($this->submissionSearch && $user->isBrandAdmin(), fn ($q) => $q->where(function ($q2) {
                    $s = '%'.$this->submissionSearch.'%';
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
                    challenge_task_completions.review_note, challenge_task_completions.score,
                    challenge_task_completions.rubric_payload, challenge_task_completions.submission_payload,
                    challenge_task_completions.created_at,
                    challenge_task_completions.updated_at,
                    COUNT(CASE WHEN submission_votes.vote_type = 'good' THEN 1 END) as good_vote_count,
                    COUNT(CASE WHEN submission_votes.vote_type = 'excellent' THEN 1 END) as excellent_vote_count,
                    COUNT(submission_votes.id) as vote_count")
                ->groupBy(
                    'challenge_task_completions.id', 'users.name', 'users.username', 'users.email',
                    'users.id', 'users.avatar', 'challenge_tasks.day_number', 'challenge_tasks.title',
                    'challenge_task_completions.evidence', 'challenge_task_completions.is_late',
                    'challenge_task_completions.status', 'challenge_task_completions.review_note',
                    'challenge_task_completions.score', 'challenge_task_completions.rubric_payload',
                    'challenge_task_completions.submission_payload',
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
                    ->get(['completion_id', 'status', 'note', 'score', 'rubric_payload', 'created_at'])
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
        if ($isApproved && $myMember instanceof ExpeditionMember && $myMember->personal_starts_at) {
            $adjustedHours = $this->expedition->getAdjustedHoursForMember($myMember);
            $totalHoursNeeded = $this->expedition->required_days * 24;
            $personalDaysLeft = max(0, (int) ceil(($totalHoursNeeded - $adjustedHours) / 24));
        }

        // Admin: member progress report
        $memberReport = collect();
        if (Auth::check() && $this->currentUser()->isBrandAdmin() && $tasks->count() > 0 && $approvedMembers->count() > 0) {
            $allCompletions = \DB::table('challenge_task_completions')
                ->whereIn('challenge_task_id', $tasks->pluck('id'))
                ->orderBy('created_at')
                ->get()
                ->groupBy('user_id');

            $dayByTask = $tasks->keyBy('id')->map(fn ($t) => (int) $t->day_number);

            $memberReport = $approvedMembers
                ->filter(fn ($m) => $m->personal_starts_at !== null)
                ->map(function ($member) use ($tasks, $allCompletions, $dayByTask) {
                    $memberDay = $this->expedition->getCurrentDayForMember($member);
                    $completed = $allCompletions->get($member->user_id, collect());
                    // Bài chính = row đầu tiên per task (theo created_at). Các row sau = mini-game entries, KHÔNG đếm vô báo cáo.
                    $mainSubs = $completed->groupBy('challenge_task_id')->map(fn ($rows) => $rows->first());
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
                        if ($row->status !== 'approved') {
                            continue;
                        }
                        $day = $dayByTask[$row->challenge_task_id] ?? null;
                        if ($day === null) {
                            continue;
                        }
                        $approvedDayLateMap[$day] = ($approvedDayLateMap[$day] ?? false) || (bool) $row->is_late;
                    }
                    $missingPastDeadline = 0;
                    for ($d = 1; $d < $memberDay; $d++) {
                        if (! array_key_exists($d, $approvedDayLateMap)) {
                            $missingPastDeadline++;
                        }
                    }
                    $lateInApproved = false;
                    foreach ($approvedDayLateMap as $d => $wasLate) {
                        if ($d <= $memberDay && $wasLate) {
                            $lateInApproved = true;
                            break;
                        }
                    }

                    return (object) [
                        'member' => $member,
                        'current_day' => $memberDay,
                        'completed' => $validCount,
                        'rejected' => $rejectedCount,
                        'late' => $lateCount,
                        'missed' => $missedCount,
                        'total' => $tasks->count(),
                        'pct' => round($validCount / max(1, $tasks->count()) * 100),
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
                fn ($r) => str_contains(mb_strtolower($r->member->user->name), $s)
            )->values();
        }

        // Summary stats for member report dashboard (computed before search filter)
        $reportStats = null;
        if ($memberReport->count() > 0) {
            $reportStats = (object) [
                'total' => $memberReport->count(),
                'on_track' => $memberReport->filter(fn ($r) => $r->missing_past_deadline === 0 && ! $r->late_in_approved && $r->completed < $r->total)->count(),
                'on_track_late' => $memberReport->filter(fn ($r) => $r->missing_past_deadline === 0 && $r->late_in_approved)->count(),
                'completed' => $memberReport->filter(fn ($r) => $r->completed === $r->total && ! $r->late_in_approved)->count(),
                'miss' => $memberReport->filter(fn ($r) => $r->missing_past_deadline > 0)->count(),
                'submissions_total' => $totalPendingSubmissions + \DB::table('challenge_task_completions')
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
        if ($isApproved && $myMember instanceof ExpeditionMember && $myMember->personal_starts_at) {
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
        ])->layout('layouts.app', ['title' => $this->expedition->title.' — Challenge']);
    }
}
