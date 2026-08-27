<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\LessonProgress;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class AcademyPage extends Component
{
    #[Url]
    public string $pillar = '';

    #[Url]
    public string $difficulty = '';

    public function setPillar(string $p): void
    {
        $this->pillar = $this->pillar === $p ? '' : $p;
    }

    public function render(): View
    {
        $query = Course::where('is_published', true);

        if ($this->pillar) {
            $query->where('pillar', $this->pillar);
        }

        if ($this->difficulty) {
            $query->where('difficulty', $this->difficulty);
        }

        $userId = Auth::id();
        $courses = $query->withCount(['enrollments', 'modules'])
            ->with([
                'modules.lessons:id,module_id',
                'enrollments' => fn ($q) => $q->when($userId, fn ($enrollment) => $enrollment->where('user_id', $userId)),
            ])
            ->orderByDesc('created_at')
            ->get();

        $lessonIds = $courses->flatMap(fn ($course) => $course->modules->flatMap(fn ($module) => $module->lessons->pluck('id')));
        $completedLessonIds = $userId && $lessonIds->isNotEmpty()
            ? LessonProgress::where('user_id', $userId)
                ->whereIn('lesson_id', $lessonIds)
                ->whereNotNull('completed_at')
                ->pluck('lesson_id')
            : collect();

        $courses->each(function ($course) use ($completedLessonIds): void {
            $courseLessonIds = $course->modules->flatMap(fn ($module) => $module->lessons->pluck('id'));
            $course->setAttribute('lesson_count', $courseLessonIds->count());
            $course->setAttribute('completed_lessons', $completedLessonIds->intersect($courseLessonIds)->count());
            $course->setAttribute('progress_percent', $courseLessonIds->count() > 0
                ? (int) round($completedLessonIds->intersect($courseLessonIds)->count() / $courseLessonIds->count() * 100)
                : 0);
            $course->setAttribute('user_enrolled', $course->enrollments->contains(fn ($enrollment) => $enrollment->status === 'active'));
        });

        return view('livewire.academy-page', ['courses' => $courses])
            ->layout('layouts.app', ['title' => 'Khóa học — '.brand()->name]);
    }
}
