<?php

namespace App\Livewire;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Community\Application\QaAnswerOutcome;
use Modules\Community\Application\QaService;

class QaPage extends Component
{
    use WithPagination;

    #[Url]
    public string $filter = 'all';

    public bool $showAsk = false;

    #[Rule('required|min:5|max:200')]
    public string $title = '';

    #[Rule('nullable|max:50000')]
    public string $body = '';

    public string $pillar = '';

    public bool $isAnonymous = false;

    public ?int $openQuestionId = null;

    public string $answerBody = '';

    public ?int $editingAnswerId = null;

    public string $editingAnswerBody = '';

    public ?int $editingQuestionId = null;

    public string $editingQuestionTitle = '';

    public string $editingQuestionBody = '';

    public function setFilter(string $f): void
    {
        $this->filter = $f;
        $this->resetPage();
    }

    public function toggleQuestion(int $id): void
    {
        $this->openQuestionId = $this->openQuestionId === $id ? null : $id;
        $this->answerBody = '';
    }

    public function submitAnswer(): void
    {
        $user = $this->authenticatedUser();
        if (! $user || ! $this->openQuestionId || blank($this->answerBody)) {
            return;
        }
        $this->validate(['answerBody' => 'required|min:3|max:5000']);
        $outcome = app(QaService::class)->submitAnswer($this->openQuestionId, $user, $this->answerBody);
        if ($outcome === QaAnswerOutcome::AlreadyAnswered) {
            $this->addError('answerBody', 'Bạn đã trả lời câu hỏi này rồi.');

            return;
        }
        if ($outcome === QaAnswerOutcome::RateLimited) {
            $this->addError('answerBody', 'Bạn đã trả lời quá nhiều. Vui lòng đợi 1 giờ.');

            return;
        }
        $this->answerBody = '';
    }

    public function editAnswer(int $id): void
    {
        $answer = Answer::findOrFail($id);
        if ($answer->user_id !== Auth::id() && ! Auth::user()?->isCommunityModerator(brand()->id)) {
            return;
        }
        $this->editingAnswerId = $id;
        $this->editingAnswerBody = $answer->body;
    }

    public function cancelEditAnswer(): void
    {
        $this->editingAnswerId = null;
        $this->editingAnswerBody = '';
    }

    public function updateAnswer(): void
    {
        if (! $this->editingAnswerId) {
            return;
        }
        $answer = Answer::findOrFail($this->editingAnswerId);
        $this->validate(['editingAnswerBody' => 'required|min:3|max:5000']);
        $user = $this->authenticatedUser();
        if (! $user) {
            return;
        }
        app(QaService::class)->updateAnswer($answer->id, $user, $this->editingAnswerBody);
        $this->editingAnswerId = null;
        $this->editingAnswerBody = '';
    }

    public function deleteAnswer(int $id): void
    {
        $answer = Answer::findOrFail($id);
        $user = $this->authenticatedUser();
        if (! $user) {
            return;
        }
        app(QaService::class)->deleteAnswer($answer->id, $user);
    }

    public function editQuestion(int $id): void
    {
        if (! Auth::user()?->isCommunityModerator(brand()->id)) {
            return;
        }
        $q = Question::findOrFail($id);
        $this->editingQuestionId = $id;
        $this->editingQuestionTitle = $q->title;
        $this->editingQuestionBody = $q->body ?? '';
    }

    public function cancelEditQuestion(): void
    {
        $this->editingQuestionId = null;
        $this->editingQuestionTitle = '';
        $this->editingQuestionBody = '';
    }

    public function updateQuestion(): void
    {
        if (! $this->editingQuestionId || ! Auth::user()?->isCommunityModerator(brand()->id)) {
            return;
        }
        $this->validate([
            'editingQuestionTitle' => 'required|min:5|max:200',
            'editingQuestionBody' => 'nullable|max:50000',
        ]);
        $user = $this->authenticatedUser();
        if (! $user) {
            return;
        }
        app(QaService::class)->updateQuestion($this->editingQuestionId, $user, $this->editingQuestionTitle, $this->editingQuestionBody ?: null);
        $this->cancelEditQuestion();
    }

    public function deleteQuestion(int $id): void
    {
        if (! Auth::user()?->isCommunityModerator(brand()->id)) {
            return;
        }
        $user = $this->authenticatedUser();
        if (! $user) {
            return;
        }
        app(QaService::class)->deleteQuestion($id, $user);
        if ($this->openQuestionId === $id) {
            $this->openQuestionId = null;
        }
    }

    public function submitQuestion(): void
    {
        $this->validate();

        $throttleKey = 'submit-question:'.Auth::id();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('title', 'Bạn đang đặt câu hỏi quá nhanh. Vui lòng thử lại sau '.round($seconds / 60).' phút.');

            return;
        }
        $user = $this->authenticatedUser();
        if (! $user) {
            return;
        }
        app(QaService::class)->submitQuestion($user, [
            'title' => $this->title,
            'body' => $this->body ?: null,
            'pillar' => $this->pillar ?: null,
            'isAnonymous' => $this->isAnonymous,
        ]);
        RateLimiter::hit($throttleKey, 3600); // 5 questions per hour

        $this->reset(['title', 'body', 'pillar', 'isAnonymous', 'showAsk']);
    }

    private function authenticatedUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    public function render(): View
    {
        $query = Question::with(['user', 'answers.user'])->withCount('answers');
        match ($this->filter) {
            'unanswered' => $query->where('status', 'open'),
            'answered' => $query->where('status', 'answered'),
            'mine' => $query->where('user_id', Auth::id()),
            default => null,
        };

        return view('livewire.qa-page', ['questions' => $query->latest()->paginate(10)])
            ->layout('layouts.app', ['title' => 'Hỏi đáp kỹ thuật — DSCons']);
    }
}
