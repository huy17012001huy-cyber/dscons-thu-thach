<?php

namespace App\Livewire;

use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Modules\Learning\Application\CourseCurriculumManagementService;

class AdminCourseBuilder extends Component
{
    public Course $course;

    // Module form
    public bool $showAddModule = false;

    #[Rule('required|min:3|max:100')]
    public string $moduleName = '';

    // Lesson form
    public ?int $addLessonToModule = null;

    #[Rule('required|min:3|max:150')]
    public string $lessonTitle = '';

    public string $lessonType = 'lecture';

    public int $lessonXp = 25;

    public bool $lessonLocked = true;

    // Task form
    public ?int $addTaskToLesson = null;

    #[Rule('required|min:3|max:200')]
    public string $taskTitle = '';

    public string $taskDescription = '';

    public string $taskType = 'text';

    public bool $taskRequired = true;

    public function mount(int $id): void
    {
        $this->course = Course::with(['modules.lessons.tasks'])->findOrFail($id);
    }

    public function addModule(): void
    {
        if (! Auth::user()?->isBrandAdmin()) {
            return;
        }
        $this->validate(['moduleName' => 'required|min:3|max:100']);
        app(CourseCurriculumManagementService::class)->addModule($this->course, Auth::user(), $this->moduleName);
        $this->reset(['moduleName', 'showAddModule']);
        $this->course->refresh();
    }

    public function deleteModule(int $id): void
    {
        if (! Auth::user()?->isBrandAdmin()) {
            return;
        }
        app(CourseCurriculumManagementService::class)->deleteModule($this->course, Auth::user(), $id);
        $this->course->refresh();
    }

    public function addLesson(): void
    {
        if (! Auth::user()?->isBrandAdmin()) {
            return;
        }
        $this->validate(['lessonTitle' => 'required|min:3|max:150']);
        if (! $this->addLessonToModule) {
            return;
        }

        app(CourseCurriculumManagementService::class)->addLesson($this->course, Auth::user(), $this->addLessonToModule, [
            'title' => $this->lessonTitle,
            'lesson_type' => $this->lessonType,
            'xp_reward' => $this->lessonXp,
            'is_locked_by_default' => $this->lessonLocked,
        ]);
        $this->reset(['lessonTitle', 'lessonType', 'lessonXp', 'lessonLocked', 'addLessonToModule']);
        $this->lessonType = 'lecture';
        $this->lessonXp = 25;
        $this->lessonLocked = true;
        $this->course->refresh();
    }

    public function deleteLesson(int $id): void
    {
        if (! Auth::user()?->isBrandAdmin()) {
            return;
        }
        app(CourseCurriculumManagementService::class)->deleteLesson($this->course, Auth::user(), $id);
        $this->course->refresh();
    }

    public function addTask(): void
    {
        if (! Auth::user()?->isBrandAdmin()) {
            return;
        }
        $this->validate(['taskTitle' => 'required|min:3|max:200']);
        if (! $this->addTaskToLesson) {
            return;
        }

        app(CourseCurriculumManagementService::class)->addTask($this->course, Auth::user(), $this->addTaskToLesson, [
            'title' => $this->taskTitle,
            'description' => $this->taskDescription ?: null,
            'type' => $this->taskType,
            'is_required' => $this->taskRequired,
        ]);
        $this->reset(['taskTitle', 'taskDescription', 'taskType', 'taskRequired', 'addTaskToLesson']);
        $this->taskType = 'text';
        $this->taskRequired = true;
        $this->course->refresh();
    }

    public function deleteTask(int $id): void
    {
        if (! Auth::user()?->isBrandAdmin()) {
            return;
        }
        app(CourseCurriculumManagementService::class)->deleteTask($this->course, Auth::user(), $id);
        $this->course->refresh();
    }

    public function render(): View
    {
        return view('livewire.admin-course-builder')
            ->layout('layouts.app', ['title' => 'Xây dựng: '.$this->course->title.' — Admin']);
    }
}
