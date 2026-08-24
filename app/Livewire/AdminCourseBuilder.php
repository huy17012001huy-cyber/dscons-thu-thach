<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonTask;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Component;

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
        if (!Auth::user()?->isBrandAdmin()) return;
        $this->validate(['moduleName' => 'required|min:3|max:100']);
        $maxOrder = $this->course->modules()->max('order_index') ?? -1;
        Module::create([
            'course_id' => $this->course->id,
            'title' => $this->moduleName,
            'order_index' => $maxOrder + 1,
        ]);
        $this->reset(['moduleName', 'showAddModule']);
        $this->course->refresh();
    }

    public function deleteModule(int $id): void
    {
        if (!Auth::user()?->isBrandAdmin()) return;
        Module::where('id', $id)->where('course_id', $this->course->id)->delete();
        $this->course->refresh();
    }

    public function addLesson(): void
    {
        if (!Auth::user()?->isBrandAdmin()) return;
        $this->validate(['lessonTitle' => 'required|min:3|max:150']);
        if (!$this->addLessonToModule) return;

        $maxOrder = Lesson::where('module_id', $this->addLessonToModule)->max('order_index') ?? -1;
        Lesson::create([
            'module_id' => $this->addLessonToModule,
            'title' => $this->lessonTitle,
            'lesson_type' => $this->lessonType,
            'xp_reward' => $this->lessonXp,
            'order_index' => $maxOrder + 1,
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
        if (!Auth::user()?->isBrandAdmin()) return;
        Lesson::findOrFail($id)->delete();
        $this->course->refresh();
    }

    public function addTask(): void
    {
        if (!Auth::user()?->isBrandAdmin()) return;
        $this->validate(['taskTitle' => 'required|min:3|max:200']);
        if (!$this->addTaskToLesson) return;

        $maxOrder = LessonTask::where('lesson_id', $this->addTaskToLesson)->max('order_index') ?? -1;
        LessonTask::create([
            'lesson_id' => $this->addTaskToLesson,
            'title' => $this->taskTitle,
            'description' => $this->taskDescription ?: null,
            'type' => $this->taskType,
            'order_index' => $maxOrder + 1,
            'is_required' => $this->taskRequired,
        ]);
        $this->reset(['taskTitle', 'taskDescription', 'taskType', 'taskRequired', 'addTaskToLesson']);
        $this->taskType = 'text';
        $this->taskRequired = true;
        $this->course->refresh();
    }

    public function deleteTask(int $id): void
    {
        if (!Auth::user()?->isBrandAdmin()) return;
        LessonTask::findOrFail($id)->delete();
        $this->course->refresh();
    }

    public function render()
    {
        return view('livewire.admin-course-builder')
            ->layout('layouts.app', ['title' => 'Xây dựng: ' . $this->course->title . ' — Admin']);
    }
}
