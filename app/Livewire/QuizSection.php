<?php

namespace App\Livewire;

use App\Models\ChallengeTask;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Modules\Learning\Application\QuizAttemptService;

/**
 * Renders an inline interactive quiz embedded inside the challenge task panel.
 * Quiz definition lives on $task->quiz_json. Attempts persist per-user via QuizAttempt.
 *
 * State:
 *   $selected[idx]  = letter (A/B/C/D) — local pick before user clicks "Kiểm tra đáp án"
 *   $submitted[idx] = ['letter' => 'A', 'is_correct' => true] — saved attempt; presence locks options
 *
 * Retry: clear $submitted[idx] and $selected[idx] → user can re-pick. UPSERT on resubmit.
 * XP: awarded once per (user, task, q_idx) on first correct via xp_awarded flag.
 */
class QuizSection extends Component
{
    #[Locked]
    public ChallengeTask $task;

    /** @var array<int, array{letter: string, is_correct: bool}> */
    public array $submitted = [];

    public function mount(ChallengeTask $task): void
    {
        $this->task = $task;

        if (! Auth::check()) {
            return;
        }

        // Only load persisted attempts for actual challenge members.
        // Admin previewing without joining → fresh state, no persistence.
        if (! $this->isPersistingMember()) {
            return;
        }

        $attempts = QuizAttempt::where('user_id', Auth::id())
            ->where('challenge_task_id', $task->id)
            ->get();

        foreach ($attempts as $a) {
            $this->submitted[$a->question_index] = [
                'letter' => $a->selected_letter,
                'is_correct' => (bool) $a->is_correct,
            ];
        }
    }

    private function isPersistingMember(): bool
    {
        $user = $this->authenticatedUser();
        if (! $user || ! $this->task->expedition) {
            return false;
        }

        return $this->task->expedition->members()
            ->where('user_id', $user->id)
            ->whereIn('status', ['approved', 'paid'])
            ->whereNull('kicked_at')
            ->exists();
    }

    /**
     * Single-click commit: chọn đáp án = nộp luôn. Không có nút "Kiểm tra đáp án" rời + không retry
     * → tránh exploit "đoán mò → reveal đáp án → retry chọn đúng để farm XP".
     * Một câu chỉ có một lần submit duy nhất; đáp án chốt vĩnh viễn ngay khi click.
     */
    public function answerQuestion(int $idx, string $letter): void
    {
        $user = $this->authenticatedUser();
        if (! $user) {
            return;
        }
        if (isset($this->submitted[$idx])) {
            return;
        }
        if (! in_array($letter, ['A', 'B', 'C', 'D'])) {
            return;
        }

        $questions = $this->task->quiz_json ?? [];
        if (! isset($questions[$idx])) {
            return;
        }

        $isCorrect = $letter === ($questions[$idx]['correct'] ?? null);
        if ($this->isPersistingMember()) {
            $result = app(QuizAttemptService::class)->answer($this->task, $user, $idx, $letter);
            $letter = $result->selectedLetter;
            $isCorrect = $result->isCorrect;
            if ($result->xpAwarded) {
                $this->dispatch('toast', message: '+2 XP — câu '.($idx + 1).' chính xác!', type: 'success');
            }
        }

        $this->submitted[$idx] = ['letter' => $letter, 'is_correct' => $isCorrect];
    }

    public function getProgressProperty(): int
    {
        return count($this->submitted);
    }

    public function getScoreProperty(): int
    {
        return count(array_filter($this->submitted, fn (array $submission): bool => $submission['is_correct']));
    }

    public function getTotalProperty(): int
    {
        return count($this->task->quiz_json ?? []);
    }

    private function authenticatedUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    public function render(): View
    {
        return view('livewire.quiz-section');
    }
}
