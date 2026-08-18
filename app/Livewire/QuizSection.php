<?php

namespace App\Livewire;

use App\Models\ChallengeTask;
use App\Models\QuizAttempt;
use App\Services\XpService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

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

    public array $submitted = [];

    public function mount(ChallengeTask $task): void
    {
        $this->task = $task;

        if (!Auth::check()) return;

        // Only load persisted attempts for actual challenge members.
        // Admin previewing without joining → fresh state, no persistence.
        if (!$this->isPersistingMember()) return;

        $attempts = QuizAttempt::where('user_id', Auth::id())
            ->where('challenge_task_id', $task->id)
            ->get();

        foreach ($attempts as $a) {
            $this->submitted[$a->question_index] = [
                'letter'     => $a->selected_letter,
                'is_correct' => (bool) $a->is_correct,
            ];
        }
    }

    private function isPersistingMember(): bool
    {
        if (!Auth::check()) return false;
        return $this->task->expedition->members()
            ->where('user_id', Auth::id())
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
        if (!Auth::check()) return;
        if (isset($this->submitted[$idx])) return;
        if (!in_array($letter, ['A', 'B', 'C', 'D'])) return;

        $questions = $this->task->quiz_json ?? [];
        if (!isset($questions[$idx])) return;

        $isCorrect = ($letter === ($questions[$idx]['correct'] ?? null));

        // Persist + award XP only for real members. Admin/non-members → in-memory preview only.
        if ($this->isPersistingMember()) {
            // firstOrCreate guard: nếu DB đã có row (somehow), không award lại — chống mọi đường tránh
            $attempt = QuizAttempt::firstOrNew([
                'user_id'           => Auth::id(),
                'challenge_task_id' => $this->task->id,
                'question_index'    => $idx,
            ]);
            $isFirstAttempt = !$attempt->exists;
            $attempt->selected_letter = $letter;
            $attempt->is_correct      = $isCorrect;
            $attempt->answered_at     = now();
            if ($isCorrect && $isFirstAttempt) {
                app(XpService::class)->award(
                    Auth::user(),
                    'quiz_correct',
                    1.0,
                    'Quiz Day '.$this->task->day_number.' · Câu '.($idx + 1),
                    $this->task
                );
                $attempt->xp_awarded = true;
                $this->dispatch('toast', message: '+2 XP — câu '.($idx + 1).' chính xác!', type: 'success');
            }
            $attempt->save();
        }

        $this->submitted[$idx] = ['letter' => $letter, 'is_correct' => $isCorrect];
    }

    public function getProgressProperty(): int
    {
        return count($this->submitted);
    }

    public function getScoreProperty(): int
    {
        return count(array_filter($this->submitted, fn ($s) => $s['is_correct'] ?? false));
    }

    public function getTotalProperty(): int
    {
        return count($this->task->quiz_json ?? []);
    }

    public function render()
    {
        return view('livewire.quiz-section');
    }
}
