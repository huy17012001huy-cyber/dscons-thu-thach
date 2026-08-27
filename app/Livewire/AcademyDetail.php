<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\LessonProgress;
use App\Models\LessonTask;
use App\Models\TaskSubmission;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Modules\Commerce\Application\CourseEnrollmentOutcome;
use Modules\Commerce\Application\CourseEnrollmentService;
use Modules\Learning\Application\CourseLearningService;
use Modules\Learning\Application\CourseLessonCompletionOutcome;

class AcademyDetail extends Component
{
    public Course $course;

    public bool $enrolled = false;

    public bool $pendingPayment = false;

    public bool $premiumLocked = false;

    public int $completedLessons = 0;

    public int $totalLessons = 0;

    public ?int $openLessonId = null;

    #[Rule('required|min:3|max:5000')]
    public string $taskAnswer = '';

    public ?int $activeTaskId = null;

    public function mount(int $id): void
    {
        $this->course = Course::with(['modules.lessons.tasks'])->findOrFail($id);
        $this->totalLessons = $this->course->modules->sum(fn ($m) => $m->lessons->count());

        if ($user = $this->authenticatedUser()) {
            $enrollment = CourseEnrollment::where('user_id', $user->id)
                ->where('course_id', $this->course->id)
                ->first();
            $this->enrolled = $enrollment && $enrollment->status === 'active';
            $this->pendingPayment = $enrollment && $enrollment->status === 'pending_payment';
            // Membership opens every course automatically, but a non-member
            // can still purchase this course individually.
            $this->premiumLocked = false;

            // Premium membership includes every course in the current community.
            // Create the enrollment lazily so progress, completion and XP keep
            // using the existing course flow without a second payment.
            if (! $this->enrolled && ! $this->pendingPayment) {
                $outcome = app(CourseEnrollmentService::class)->ensurePremiumEnrollment($this->course, $user);
                if (in_array($outcome, [CourseEnrollmentOutcome::Active, CourseEnrollmentOutcome::AlreadyActive], true)) {
                    $this->enrolled = true;
                }
            }

            if ($this->enrolled) {
                $lessonIds = $this->course->modules->flatMap(fn ($m) => $m->lessons->pluck('id'));
                $this->completedLessons = LessonProgress::where('user_id', $user->id)
                    ->whereIn('lesson_id', $lessonIds)
                    ->whereNotNull('completed_at')
                    ->count();
            }
        }
    }

    public function enroll(): void
    {
        $user = $this->authenticatedUser();
        if (! $user || $this->enrolled) {
            return;
        }

        $outcome = app(CourseEnrollmentService::class)->enroll($this->course, $user);

        match ($outcome) {
            CourseEnrollmentOutcome::Active => $this->activateEnrollment('Đăng ký khóa học thành công!'),
            CourseEnrollmentOutcome::PendingPayment => $this->markPaymentPending(),
            CourseEnrollmentOutcome::AlreadyActive => $this->enrolled = true,
            CourseEnrollmentOutcome::AlreadyPending => $this->pendingPayment = true,
            CourseEnrollmentOutcome::LevelLocked => $this->dispatch('toast', message: 'Cần đạt Lv.'.$this->course->min_level.' để tham gia', type: 'error'),
            CourseEnrollmentOutcome::Unavailable => $this->dispatch('toast', message: 'Khóa học hiện chưa mở đăng ký trong cộng đồng này.', type: 'error'),
        };
    }

    private function activateEnrollment(string $message): void
    {
        $this->enrolled = true;
        $this->pendingPayment = false;
        $this->dispatch('toast', message: $message, type: 'success');
    }

    private function markPaymentPending(): void
    {
        $this->pendingPayment = true;
        $this->dispatch('toast', message: 'Vui lòng chuyển khoản để hoàn tất đăng ký!', type: 'info');
    }

    public function toggleLesson(int $lessonId): void
    {
        $this->openLessonId = $this->openLessonId === $lessonId ? null : $lessonId;
        $this->resetTaskForm();
    }

    public function startTask(int $taskId): void
    {
        $this->activeTaskId = $taskId;
        $this->taskAnswer = '';
    }

    public function submitTask(): void
    {
        $user = $this->authenticatedUser();
        if (! $user || ! $this->enrolled || ! $this->activeTaskId) {
            return;
        }

        $this->validate();

        $result = app(CourseLearningService::class)->submitTask(
            $this->course,
            $this->activeTaskId,
            $user,
            $this->taskAnswer,
        );
        if (! $result->accepted) {
            return;
        }

        $this->resetTaskForm();
        $this->applyLessonCompletion($result->completion);
    }

    public function completeLesson(int $lessonId): void
    {
        $user = $this->authenticatedUser();
        if (! $user || ! $this->enrolled) {
            return;
        }

        $outcome = app(CourseLearningService::class)->complete($this->course, $lessonId, $user);
        $this->applyLessonCompletion($outcome);
    }

    private function applyLessonCompletion(CourseLessonCompletionOutcome $outcome): void
    {
        if (in_array($outcome, [CourseLessonCompletionOutcome::Completed, CourseLessonCompletionOutcome::CourseCompleted], true)) {
            $this->completedLessons++;
        }
        if ($outcome === CourseLessonCompletionOutcome::CourseCompleted) {
            $this->dispatch('toast', message: 'Chúc mừng! Bạn đã hoàn thành khóa học!', type: 'success');
        }
    }

    private function resetTaskForm(): void
    {
        $this->activeTaskId = null;
        $this->taskAnswer = '';
        $this->resetValidation();
    }

    private function authenticatedUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    public function render(): View
    {
        $user = $this->authenticatedUser();

        // Auto-detect payment success while polling
        if ($this->pendingPayment && $user) {
            $enrollment = CourseEnrollment::where('user_id', $user->id)
                ->where('course_id', $this->course->id)
                ->first();
            if ($enrollment && $enrollment->status === 'active') {
                $this->enrolled = true;
                $this->pendingPayment = false;
                $this->dispatch('toast', message: 'Thanh toán thành công! Khóa học đã được mở.', type: 'success');
            }
        }

        $completedIds = [];
        $submittedTaskIds = [];
        $submissions = collect();

        if ($user && $this->enrolled) {
            $lessonIds = $this->course->modules->flatMap(fn ($m) => $m->lessons->pluck('id'));
            $completedIds = LessonProgress::where('user_id', $user->id)
                ->whereIn('lesson_id', $lessonIds)
                ->whereNotNull('completed_at')
                ->pluck('lesson_id')
                ->toArray();

            $taskIds = $this->course->modules
                ->flatMap(fn ($m) => $m->lessons)
                ->flatMap(fn ($l) => $l->tasks->pluck('id'));

            $submissions = TaskSubmission::where('user_id', $user->id)
                ->whereIn('lesson_task_id', $taskIds)
                ->get()
                ->keyBy('lesson_task_id');

            $submittedTaskIds = $submissions->pluck('lesson_task_id')->toArray();
        }

        // Build unlock map
        $unlockedIds = [];
        if ($user && $this->enrolled) {
            foreach ($this->course->modules as $module) {
                foreach ($module->lessons as $lesson) {
                    if ($lesson->isUnlockedFor($user)) {
                        $unlockedIds[] = $lesson->id;
                    }
                }
            }
        }

        // Class submissions (other students' submissions for current open lesson)
        $classSubmissions = collect();
        if ($this->openLessonId) {
            $taskIdsForLesson = LessonTask::where('lesson_id', $this->openLessonId)->pluck('id');
            if ($taskIdsForLesson->isNotEmpty()) {
                $classSubmissions = TaskSubmission::whereIn('lesson_task_id', $taskIdsForLesson)
                    ->where('user_id', '!=', Auth::id() ?? 0)
                    ->with('user')
                    ->select('user_id')
                    ->distinct()
                    ->limit(10)
                    ->get();
            }
        }

        return view('livewire.academy-detail', [
            'completedIds' => $completedIds,
            'unlockedIds' => $unlockedIds,
            'submittedTaskIds' => $submittedTaskIds,
            'submissions' => $submissions,
            'classSubmissions' => $classSubmissions,
        ])->layout('layouts.app', ['title' => $this->course->title.' — Khóa học']);
    }
}
