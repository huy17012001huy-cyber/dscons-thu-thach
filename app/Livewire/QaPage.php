<?php

namespace App\Livewire;

use App\Models\Answer;
use App\Models\Question;
use App\Services\XpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

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

    public function setFilter(string $f)
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
        if (! Auth::check() || ! $this->openQuestionId || blank($this->answerBody)) {
            return;
        }
        $this->validate(['answerBody' => 'required|min:3|max:5000']);
        $q = Question::findOrFail($this->openQuestionId);

        // Anti-spam: max 1 answer per question per user
        $existing = Answer::where('question_id', $q->id)->where('user_id', Auth::id())->exists();
        if ($existing) {
            $this->addError('answerBody', 'Bạn đã trả lời câu hỏi này rồi.');

            return;
        }

        // Anti-spam: max 10 answers per hour
        $recentCount = Answer::where('user_id', Auth::id())
            ->where('created_at', '>=', now()->subHour())->count();
        if ($recentCount >= 10) {
            $this->addError('answerBody', 'Bạn đã trả lời quá nhiều. Vui lòng đợi 1 giờ.');

            return;
        }

        Answer::create(['question_id' => $q->id, 'user_id' => Auth::id(), 'body' => $this->answerBody]);
        if ($q->status === 'open') {
            $q->update(['status' => 'answered']);
        }
        app(XpService::class)->award(Auth::user(), 'comment', 1.0, 'Trả lời câu hỏi', $q);
        $this->answerBody = '';
    }

    public function editAnswer(int $id): void
    {
        $answer = Answer::findOrFail($id);
        if ($answer->user_id !== Auth::id() && ! Auth::user()?->is_admin) {
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
        if ($answer->user_id !== Auth::id() && ! Auth::user()?->is_admin) {
            return;
        }
        $this->validate(['editingAnswerBody' => 'required|min:3|max:5000']);
        $answer->update(['body' => $this->editingAnswerBody]);
        $this->editingAnswerId = null;
        $this->editingAnswerBody = '';
    }

    public function deleteAnswer(int $id): void
    {
        $answer = Answer::findOrFail($id);
        if ($answer->user_id !== Auth::id() && ! Auth::user()?->is_admin) {
            return;
        }
        $questionId = $answer->question_id;
        $answer->delete();

        $remaining = Answer::where('question_id', $questionId)->count();
        if ($remaining === 0) {
            Question::where('id', $questionId)->update(['status' => 'open']);
        }
    }

    public function editQuestion(int $id): void
    {
        if (! Auth::user()?->is_admin) {
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
        if (! $this->editingQuestionId || ! Auth::user()?->is_admin) {
            return;
        }
        $this->validate([
            'editingQuestionTitle' => 'required|min:5|max:200',
            'editingQuestionBody' => 'nullable|max:50000',
        ]);
        $q = Question::findOrFail($this->editingQuestionId);
        $q->update([
            'title' => $this->editingQuestionTitle,
            'body' => $this->editingQuestionBody ?: null,
        ]);
        $this->cancelEditQuestion();
    }

    public function deleteQuestion(int $id): void
    {
        if (! Auth::user()?->is_admin) {
            return;
        }
        Question::findOrFail($id)->delete(); // câu trả lời cascade theo FK
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
        $q = Question::create([
            'user_id' => Auth::id(), 'title' => $this->title, 'body' => $this->body,
            'pillar' => $this->pillar ?: null, 'is_anonymous' => $this->isAnonymous,
        ]);
        RateLimiter::hit($throttleKey, 3600); // 5 questions per hour

        app(XpService::class)->award(Auth::user(), 'post', 0.33, 'Đặt câu hỏi', $q);
        $this->reset(['title', 'body', 'pillar', 'isAnonymous', 'showAsk']);
    }

    public function render()
    {
        $query = Question::with(['user', 'answers.user'])->withCount('answers');
        match ($this->filter) {
            'unanswered' => $query->where('status', 'open'),
            'answered' => $query->where('status', 'answered'),
            'mine' => $query->where('user_id', Auth::id()),
            default => null,
        };

        return view('livewire.qa-page', ['questions' => $query->latest()->paginate(10)])
            ->layout('layouts.app', ['title' => 'Hỏi đáp — The All In Plan™']);
    }
}
