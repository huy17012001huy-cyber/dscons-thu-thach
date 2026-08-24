<?php

namespace App\Livewire;

use App\Models\ChallengeTask;
use App\Models\Expedition;
use App\Models\QuizAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class AdminChallenges extends Component
{
    use WithFileUploads;

    // ─── Expedition form ─────────────────────────────────────────────
    public bool $showExpeditionModal = false;
    public ?int $editingExpeditionId = null;

    #[Rule('required|string|max:255')]
    public string $expTitle = '';
    public string $expSlug = '';
    public string $expDescription = '';
    public $expCover = null;
    public ?string $existingExpCover = null;
    public bool $removeExpCover = false;
    public string $expBossName = '';
    public ?int $expLeaderId = null;
    #[Rule('required|in:normal,hard,chaos')]
    public string $expDifficulty = 'normal';
    #[Rule('required|integer|min:1')]
    public int $expRequiredDays = 21;
    #[Rule('required|integer|min:1')]
    public int $expMaxMembers = 999;
    public int $expDepositAip = 0;
    public string $expPrice = '';
    #[Rule('required|in:open,active,completed,failed')]
    public string $expStatus = 'open';
    public bool $expFeatured = false;
    public string $expStartsAt = '';
    public string $expEndsAt = '';

    // ─── Freeze modal ────────────────────────────────────────────────
    public bool $showFreezeModal = false;
    public ?int $freezingExpeditionId = null;
    #[Rule('required|integer|min:1')]
    public int $freezeFromDay = 13;
    #[Rule('required')]
    public string $freezeUntil = '';

    // ─── Quiz report (inline) ────────────────────────────────────────
    public ?int $quizReportTaskId = null;

    // ─── Task management ─────────────────────────────────────────────
    public ?int $managingExpeditionId = null;
    public bool $showTaskModal = false;
    public ?int $editingTaskId = null;

    #[Rule('required|integer|min:1')]
    public int $taskDayNumber = 1;
    public string $taskLabel = '';
    #[Rule('required|string|max:255')]
    public string $taskTitle = '';
    public string $taskDescription = '';
    public string $taskSopContent = '';
    public string $taskInstructionJson = '';
    public string $taskVideoUrl = '';
    public string $taskMeetingAt = '';
    #[Rule('required|in:text,screenshot')]
    public string $taskEvidenceType = 'text';
    public string $taskEvidenceLabel = '';
    public string $taskAdminNote = '';
    public bool $taskIsContest = false;
    public string $taskContestTitle = '';
    public string $taskContestDescription = '';
    #[Rule('required|integer|min:1')]
    public int $taskDurationHours = 24;
    public ?int $taskContestDurationHours = null;
    public string $taskQuizJson = '';
    public $taskRewardFile = null; // uploaded file (Livewire temp upload)
    public string $taskRewardFileLabel = '';
    public ?string $existingRewardFile = null; // path of file already saved (for display)

    // ─── Expedition CRUD ─────────────────────────────────────────────
    public function openCreateExpedition(): void
    {
        $this->resetExpeditionForm();
        $this->editingExpeditionId = null;
        $this->showExpeditionModal = true;
    }

    public function openEditExpedition(int $id): void
    {
        $exp = Expedition::findOrFail($id);
        $this->editingExpeditionId = $id;
        $this->expTitle        = $exp->title;
        $this->expSlug         = $exp->slug;
        $this->expDescription  = $exp->description ?? '';
        $this->expCover        = null;
        $this->existingExpCover = $exp->cover_path;
        $this->removeExpCover  = false;
        $this->expBossName     = $exp->boss_name ?? '';
        $this->expLeaderId     = $exp->leader_id;
        $this->expDifficulty   = $exp->difficulty;
        $this->expRequiredDays = $exp->required_days;
        $this->expMaxMembers   = $exp->max_members;
        $this->expDepositAip   = $exp->deposit_aip ?? 0;
        $this->expPrice        = $exp->price ? (string) $exp->price : '';
        $this->expStatus       = $exp->status;
        $this->expFeatured     = (bool) $exp->is_featured;
        $this->expStartsAt     = $exp->starts_at?->format('Y-m-d\TH:i') ?? '';
        $this->expEndsAt       = $exp->ends_at?->format('Y-m-d\TH:i') ?? '';
        $this->showExpeditionModal = true;
    }

    public function saveExpedition(): void
    {
        if (!Auth::user()?->isBrandAdmin()) return;
        $this->validate([
            'expTitle'        => 'required|string|max:255',
            'expBossName'     => 'required|string|max:255',
            'expLeaderId'     => 'required|integer|exists:users,id',
            'expDifficulty'   => 'required|in:normal,hard,chaos',
            'expRequiredDays' => 'required|integer|min:1',
            'expMaxMembers'   => 'required|integer|min:1',
            'expStatus'       => 'required|in:open,active,completed,failed',
            'expCover'        => 'nullable|image|max:8192',
        ]);

        $expedition = $this->editingExpeditionId
            ? Expedition::findOrFail($this->editingExpeditionId)
            : null;
        $oldCoverPath = $expedition?->cover_path;

        $data = [
            'title'         => $this->expTitle,
            'slug'          => $this->expSlug ?: Str::slug($this->expTitle),
            'description'   => $this->expDescription ?: null,
            'boss_name'     => $this->expBossName ?: null,
            'leader_id'     => $this->expLeaderId ?: null,
            'difficulty'    => $this->expDifficulty,
            'required_days' => $this->expRequiredDays,
            'max_members'   => $this->expMaxMembers,
            'deposit_aip'   => $this->expDepositAip,
            'price'         => $this->expPrice !== '' ? $this->expPrice : 0,
            'status'        => $this->expStatus,
            'is_featured'   => $this->expFeatured,
            'starts_at'     => $this->expStartsAt ?: null,
            'ends_at'       => $this->expEndsAt ?: null,
            'created_by'    => Auth::id(),
        ];

        if ($this->expCover) {
            $data['cover_path'] = $this->expCover->store('challenge/covers', 'public');
        } elseif ($this->removeExpCover) {
            $data['cover_path'] = null;
        }

        if ($expedition) {
            $expedition->update($data);
            $this->dispatch('toast', message: 'Đã cập nhật challenge', type: 'success');
        } else {
            Expedition::create($data);
            $this->dispatch('toast', message: 'Đã tạo challenge mới', type: 'success');
        }

        $newCoverPath = $data['cover_path'] ?? $oldCoverPath;
        if ($oldCoverPath && $oldCoverPath !== $newCoverPath && str_starts_with($oldCoverPath, 'challenge/covers/')) {
            Storage::disk('public')->delete($oldCoverPath);
        }

        $this->showExpeditionModal = false;
        $this->resetExpeditionForm();
    }

    public function deleteExpedition(int $id): void
    {
        if (!Auth::user()?->isBrandAdmin()) return;
        $expedition = Expedition::findOrFail($id);
        if ($expedition->cover_path && str_starts_with($expedition->cover_path, 'challenge/covers/')) {
            Storage::disk('public')->delete($expedition->cover_path);
        }
        $expedition->delete();
        if ($this->managingExpeditionId === $id) $this->managingExpeditionId = null;
        $this->dispatch('toast', message: 'Đã xóa challenge', type: 'success');
    }

    public function toggleManageTasks(int $id): void
    {
        $this->managingExpeditionId = $this->managingExpeditionId === $id ? null : $id;
        $this->showTaskModal = false;
    }

    // ─── Freeze (tạm dừng) ───────────────────────────────────────────
    public function openFreezeModal(int $id): void
    {
        $exp = Expedition::findOrFail($id);
        $this->freezingExpeditionId = $id;
        $this->freezeFromDay = $exp->freeze_from_day ?? 13;
        $this->freezeUntil = $exp->freeze_ends_at
            ? $exp->freeze_ends_at->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d\TH:i')
            : now('Asia/Ho_Chi_Minh')->addDays(2)->format('Y-m-d\T06:00');
        $this->showFreezeModal = true;
    }

    public function saveFreeze(): void
    {
        if (!Auth::user()?->isBrandAdmin()) return;
        $this->validate([
            'freezeFromDay' => 'required|integer|min:1',
            'freezeUntil'   => 'required|date',
        ]);

        $exp = Expedition::findOrFail($this->freezingExpeditionId);
        if ($this->freezeFromDay > $exp->required_days) {
            $this->addError('freezeFromDay', "Phải ≤ {$exp->required_days} (số ngày challenge)");
            return;
        }

        $endsAt = Carbon::parse($this->freezeUntil, 'Asia/Ho_Chi_Minh')->utc();
        if ($endsAt->lessThanOrEqualTo(now())) {
            $this->addError('freezeUntil', 'Thời điểm kết thúc phải ở tương lai');
            return;
        }

        $exp->update([
            'freeze_from_day'  => $this->freezeFromDay,
            'freeze_starts_at' => $exp->freeze_starts_at ?: now(),
            'freeze_ends_at'   => $endsAt,
        ]);

        $this->dispatch('toast', message: "Đã tạm dừng từ ngày {$this->freezeFromDay}", type: 'success');
        $this->showFreezeModal = false;
    }

    public function clearFreeze(int $id): void
    {
        if (!Auth::user()?->isBrandAdmin()) return;
        Expedition::findOrFail($id)->update([
            'freeze_from_day'  => null,
            'freeze_starts_at' => null,
            'freeze_ends_at'   => null,
        ]);
        $this->dispatch('toast', message: 'Đã bỏ đóng băng', type: 'success');
    }

    // ─── Quiz report ─────────────────────────────────────────────────
    public function toggleQuizReport(int $taskId): void
    {
        if (!Auth::user()?->isBrandAdmin()) return;
        $this->quizReportTaskId = $this->quizReportTaskId === $taskId ? null : $taskId;
    }

    /**
     * Aggregate quiz stats for a task: per-question correct rate + per-user score.
     * Single grouped query for stats, single query with users joined for scoreboard.
     */
    public function getQuizReportData(?ChallengeTask $task): array
    {
        if (!$task || empty($task->quiz_json)) {
            return ['total_users' => 0, 'per_question' => [], 'users' => [], 'total_questions' => 0];
        }

        $totalQuestions = count($task->quiz_json);

        // Per-question stats
        $rows = DB::table('quiz_attempts')
            ->select('question_index',
                DB::raw('COUNT(*) AS total'),
                DB::raw('COUNT(*) FILTER (WHERE is_correct) AS correct'))
            ->where('challenge_task_id', $task->id)
            ->groupBy('question_index')
            ->get();
        $perQuestion = [];
        foreach (range(0, $totalQuestions - 1) as $idx) {
            $row = $rows->firstWhere('question_index', $idx);
            $total = (int) ($row->total ?? 0);
            $correct = (int) ($row->correct ?? 0);
            $perQuestion[$idx] = [
                'total'   => $total,
                'correct' => $correct,
                'wrong'   => $total - $correct,
                'rate'    => $total > 0 ? $correct / $total : 0,
            ];
        }

        // Per-user scoreboard
        $userRows = DB::table('quiz_attempts')
            ->join('users', 'users.id', '=', 'quiz_attempts.user_id')
            ->select('users.id', 'users.name', 'users.username',
                DB::raw('COUNT(*) AS answered'),
                DB::raw('COUNT(*) FILTER (WHERE is_correct) AS score'))
            ->where('quiz_attempts.challenge_task_id', $task->id)
            ->groupBy('users.id', 'users.name', 'users.username')
            ->orderByDesc('score')
            ->orderByDesc('answered')
            ->get();
        $users = $userRows->map(fn ($u) => [
            'id'           => $u->id,
            'name'         => $u->name,
            'username'     => $u->username,
            'answered'     => (int) $u->answered,
            'score'        => (int) $u->score,
            'completion'   => $totalQuestions > 0 ? (int) $u->answered / $totalQuestions : 0,
        ])->all();

        return [
            'total_users'     => count($users),
            'total_questions' => $totalQuestions,
            'per_question'    => $perQuestion,
            'users'           => $users,
        ];
    }

    // ─── Task CRUD ────────────────────────────────────────────────────
    public function openCreateTask(): void
    {
        $this->resetTaskForm();
        $this->editingTaskId = null;
        // Suggest next day number
        if ($this->managingExpeditionId) {
            $max = ChallengeTask::where('expedition_id', $this->managingExpeditionId)->max('day_number');
            $this->taskDayNumber = ($max ?? 0) + 1;
        }
        $this->showTaskModal = true;
    }

    public function openEditTask(int $id): void
    {
        $task = ChallengeTask::findOrFail($id);
        $this->editingTaskId      = $id;
        $this->taskDayNumber      = $task->day_number;
        $this->taskLabel          = $task->label ?? '';
        $this->taskTitle          = $task->title;
        $this->taskDescription    = $task->description ?? '';
        $this->taskSopContent     = $task->sop_content ?? '';
        $this->taskInstructionJson = $task->instruction_payload
            ? json_encode($task->instruction_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : '';
        $this->taskVideoUrl       = $task->video_url ?? '';
        $this->taskMeetingAt      = $task->meeting_at?->format('Y-m-d\TH:i') ?? '';
        $this->taskEvidenceType   = $task->evidence_type ?? 'text';
        $this->taskEvidenceLabel  = $task->evidence_label ?? '';
        $this->taskAdminNote      = $task->admin_note ?? '';
        $this->taskIsContest            = (bool) $task->is_contest;
        $this->taskContestTitle         = $task->contest_title ?? '';
        $this->taskContestDescription   = $task->contest_description ?? '';
        $this->taskDurationHours        = $task->duration_hours ?? 24;
        $this->taskContestDurationHours = $task->contest_duration_hours;
        $this->taskRewardFile     = null;
        $this->taskRewardFileLabel = $task->reward_file_label ?? '';
        $this->existingRewardFile = $task->reward_file_path;
        $this->taskQuizJson       = $task->quiz_json
            ? json_encode($task->quiz_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : '';
        $this->showTaskModal = true;
    }

    public function saveTask(): void
    {
        if (!Auth::user()?->isBrandAdmin()) return;
        $this->validate([
            'taskDayNumber'     => 'required|integer|min:1',
            'taskTitle'         => 'required|string|max:255',
            'taskEvidenceType'  => 'required|in:text,screenshot',
            'taskDurationHours' => 'required|integer|min:1',
        ]);

        // Validate quiz_json structure if provided
        $quizJson = null;
        if (trim($this->taskQuizJson) !== '') {
            $decoded = json_decode($this->taskQuizJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->addError('taskQuizJson', 'JSON không hợp lệ: '.json_last_error_msg());
                return;
            }
            if (!is_array($decoded) || !array_is_list($decoded)) {
                $this->addError('taskQuizJson', 'Quiz phải là MẢNG (array) các câu hỏi.');
                return;
            }
            foreach ($decoded as $i => $q) {
                foreach (['q', 'options', 'correct'] as $key) {
                    if (!isset($q[$key])) {
                        $this->addError('taskQuizJson', "Câu ".($i+1).": thiếu field '{$key}'");
                        return;
                    }
                }
                if (!is_array($q['options']) || !isset($q['options']['A'], $q['options']['B'], $q['options']['C'], $q['options']['D'])) {
                    $this->addError('taskQuizJson', "Câu ".($i+1).": options phải có đủ A/B/C/D");
                    return;
                }
                if (!in_array($q['correct'], ['A', 'B', 'C', 'D'], true)) {
                    $this->addError('taskQuizJson', "Câu ".($i+1).": correct phải là A/B/C/D");
                    return;
                }
            }
            $quizJson = $decoded;
        }

        $instructionPayload = null;
        if (trim($this->taskInstructionJson) !== '') {
            $instructionPayload = json_decode($this->taskInstructionJson, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($instructionPayload)) {
                $this->addError('taskInstructionJson', 'instruction_payload phải là JSON object hợp lệ.');
                return;
            }
            foreach (['modality', 'learning_objectives', 'sop_steps', 'verification_checklist', 'homework', 'rubric', 'common_errors', 'pass_score', 'track'] as $key) {
                if (!array_key_exists($key, $instructionPayload)) {
                    $this->addError('taskInstructionJson', "Thiếu key bắt buộc: {$key}");
                    return;
                }
            }
        }

        // Validate file upload riêng (max 20MB, whitelist extension)
        if ($this->taskRewardFile) {
            $this->validate([
                'taskRewardFile' => 'file|mimes:pdf,zip,doc,docx,xlsx,png,jpg,jpeg,mp4|max:20480',
            ]);
        }

        $data = [
            'expedition_id'   => $this->managingExpeditionId,
            'day_number'      => $this->taskDayNumber,
            'label'           => $this->taskLabel ?: null,
            'title'           => $this->taskTitle,
            'description'     => $this->taskDescription ?: null,
            'sop_content'     => $this->taskSopContent ?: null,
            'video_url'       => $this->taskVideoUrl ?: null,
            'meeting_at'      => $this->taskMeetingAt ?: null,
            'evidence_type'   => $this->taskEvidenceType,
            'evidence_label'  => $this->taskEvidenceLabel ?: null,
            'admin_note'      => $this->taskAdminNote ?: null,
            'is_contest'             => $this->taskIsContest,
            'contest_title'          => $this->taskIsContest ? ($this->taskContestTitle ?: null) : null,
            'contest_description'    => $this->taskIsContest ? ($this->taskContestDescription ?: null) : null,
            'duration_hours'         => $this->taskDurationHours,
            'contest_duration_hours' => $this->taskIsContest ? ($this->taskContestDurationHours ?: null) : null,
            'reward_file_label' => $this->taskRewardFileLabel ?: null,
            'quiz_json'       => $quizJson,
            'instruction_payload' => $instructionPayload,
        ];

        if ($this->editingTaskId) {
            $task = ChallengeTask::findOrFail($this->editingTaskId);
            // Lưu file mới: xoá file cũ nếu có, rồi store file upload
            if ($this->taskRewardFile) {
                if ($task->reward_file_path && Storage::disk('local')->exists($task->reward_file_path)) {
                    Storage::disk('local')->delete($task->reward_file_path);
                }
                $ext = $this->taskRewardFile->getClientOriginalExtension();
                $stored = $this->taskRewardFile->storeAs(
                    'challenge-rewards',
                    "task-{$task->id}-" . time() . '.' . $ext,
                    'local'
                );
                $data['reward_file_path'] = $stored;
            }
            $task->update($data);
            $this->dispatch('toast', message: 'Đã cập nhật task', type: 'success');
        } else {
            $task = ChallengeTask::create($data);
            if ($this->taskRewardFile) {
                $ext = $this->taskRewardFile->getClientOriginalExtension();
                $stored = $this->taskRewardFile->storeAs(
                    'challenge-rewards',
                    "task-{$task->id}-" . time() . '.' . $ext,
                    'local'
                );
                $task->update(['reward_file_path' => $stored]);
            }
            $this->dispatch('toast', message: 'Đã thêm task mới', type: 'success');
        }

        $this->showTaskModal = false;
        $this->resetTaskForm();
    }

    public function deleteTask(int $id): void
    {
        if (!Auth::user()?->isBrandAdmin()) return;
        $task = ChallengeTask::findOrFail($id);
        if ($task->reward_file_path && Storage::disk('local')->exists($task->reward_file_path)) {
            Storage::disk('local')->delete($task->reward_file_path);
        }
        $task->delete();
        $this->dispatch('toast', message: 'Đã xóa task', type: 'success');
    }

    public function removeRewardFile(): void
    {
        if (!Auth::user()?->isBrandAdmin() || !$this->editingTaskId) return;
        $task = ChallengeTask::findOrFail($this->editingTaskId);
        if ($task->reward_file_path && Storage::disk('local')->exists($task->reward_file_path)) {
            Storage::disk('local')->delete($task->reward_file_path);
        }
        $task->update(['reward_file_path' => null, 'reward_file_label' => null]);
        $this->existingRewardFile = null;
        $this->taskRewardFileLabel = '';
        $this->dispatch('toast', message: 'Đã xoá file thưởng', type: 'success');
    }

    // ─── Helpers ─────────────────────────────────────────────────────
    private function resetExpeditionForm(): void
    {
        $this->expTitle = $this->expSlug = $this->expDescription = $this->expBossName = '';
        $this->expCover = null;
        $this->existingExpCover = null;
        $this->removeExpCover = false;
        $this->expLeaderId = null;
        $this->expDifficulty = 'normal';
        $this->expRequiredDays = 21;
        $this->expMaxMembers = 999;
        $this->expDepositAip = 0;
        $this->expPrice = $this->expStartsAt = $this->expEndsAt = '';
        $this->expStatus = 'open';
        $this->expFeatured = false;
    }

    private function resetTaskForm(): void
    {
        $this->taskDayNumber = 1;
        $this->taskLabel = '';
        $this->taskTitle = $this->taskDescription = $this->taskSopContent = '';
        $this->taskInstructionJson = '';
        $this->taskVideoUrl = $this->taskMeetingAt = $this->taskEvidenceLabel = $this->taskAdminNote = '';
        $this->taskEvidenceType = 'text';
        $this->taskIsContest = false;
        $this->taskContestTitle = '';
        $this->taskContestDescription = '';
        $this->taskDurationHours = 24;
        $this->taskContestDurationHours = null;
        $this->taskQuizJson = '';
        $this->taskRewardFile = null;
        $this->taskRewardFileLabel = '';
        $this->existingRewardFile = null;
    }

    public function render()
    {
        $expeditions = Expedition::withCount(['members', 'tasks'])->latest()->get();
        $managingTasks = $this->managingExpeditionId
            ? ChallengeTask::where('expedition_id', $this->managingExpeditionId)->orderBy('day_number')->get()
            : collect();
        $staffUsers = User::where('is_admin', true)->orWhere('is_moderator', true)
            ->orderBy('name')->get(['id', 'name', 'username']);

        return view('livewire.admin-challenges', compact('expeditions', 'managingTasks', 'staffUsers'))
            ->layout('layouts.app', ['title' => 'Quản lý Challenge — Admin']);
    }
}
