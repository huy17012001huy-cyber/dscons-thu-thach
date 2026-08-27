<?php

namespace App\Livewire;

use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class AdminCourses extends Component
{
    use WithFileUploads;

    public bool $showCourseModal = false;

    public ?int $editingCourseId = null;

    public string $courseTitle = '';

    public string $courseDescription = '';

    public string $coursePillar = 'delivery';

    public string $courseDifficulty = 'basic';

    public int $courseMinLevel = 1;

    public int $courseXpReward = 0;

    public int $courseAipReward = 0;

    public string $coursePrice = '';

    public bool $coursePublished = false;

    public bool $courseFeatured = false;

    public ?TemporaryUploadedFile $courseThumbnail = null;

    public ?string $existingCourseThumbnail = null;

    public bool $removeCourseThumbnail = false;

    public function openCreateCourse(): void
    {
        $this->resetCourseForm();
        $this->showCourseModal = true;
    }

    public function openEditCourse(int $id): void
    {
        if (! Auth::user()?->isBrandAdmin()) {
            return;
        }
        $course = Course::findOrFail($id);
        $this->editingCourseId = $course->id;
        $this->courseTitle = $course->title;
        $this->courseDescription = $course->description ?? '';
        $this->coursePillar = $course->pillar;
        $this->courseDifficulty = $course->difficulty;
        $this->courseMinLevel = $course->min_level;
        $this->courseXpReward = $course->xp_reward;
        $this->courseAipReward = $course->aip_reward;
        $this->coursePrice = $course->price > 0 ? (string) $course->price : '';
        $this->coursePublished = (bool) $course->is_published;
        $this->courseFeatured = (bool) $course->is_featured;
        $this->courseThumbnail = null;
        $this->existingCourseThumbnail = $course->thumbnail;
        $this->removeCourseThumbnail = false;
        $this->showCourseModal = true;
    }

    public function saveCourse(): void
    {
        if (! Auth::user()?->isBrandAdmin()) {
            return;
        }
        $this->validate([
            'courseTitle' => 'required|string|max:255',
            'courseDescription' => 'nullable|string|max:5000',
            'coursePillar' => 'required|in:offer,traffic,conversion,delivery,continuity',
            'courseDifficulty' => 'required|in:basic,advanced,expert',
            'courseMinLevel' => 'required|integer|min:1|max:999',
            'courseXpReward' => 'required|integer|min:0|max:1000000',
            'courseAipReward' => 'required|integer|min:0|max:1000000',
            'coursePrice' => 'nullable|integer|min:0|max:100000000',
            'courseThumbnail' => 'nullable|image|max:8192',
        ]);

        $course = $this->editingCourseId ? Course::findOrFail($this->editingCourseId) : null;
        $oldThumbnail = $course?->thumbnail;
        $data = [
            'title' => trim($this->courseTitle),
            'description' => trim($this->courseDescription) ?: null,
            'pillar' => $this->coursePillar,
            'difficulty' => $this->courseDifficulty,
            'min_level' => $this->courseMinLevel,
            'xp_reward' => $this->courseXpReward,
            'aip_reward' => $this->courseAipReward,
            'price' => $this->coursePrice !== '' ? (int) $this->coursePrice : 0,
            'is_published' => $this->coursePublished,
            'is_featured' => $this->courseFeatured,
        ];

        if ($this->courseThumbnail) {
            $path = $this->courseThumbnail->store('course/thumbnails', 'public');
            if (is_string($path)) {
                $data['thumbnail'] = $path;
            }
        } elseif ($this->removeCourseThumbnail) {
            $data['thumbnail'] = null;
        }

        if ($course) {
            $course->update($data);
        } else {
            $course = Course::create($data);
        }

        $newThumbnail = $data['thumbnail'] ?? $oldThumbnail;
        if ($oldThumbnail && $oldThumbnail !== $newThumbnail && str_starts_with($oldThumbnail, 'course/thumbnails/')) {
            Storage::disk('public')->delete($oldThumbnail);
        }

        $this->showCourseModal = false;
        $this->resetCourseForm();
        $this->dispatch('toast', message: 'Đã lưu khóa học.', type: 'success');
    }

    public function togglePublish(int $id): void
    {
        if (! Auth::user()?->isBrandAdmin()) {
            return;
        }
        $course = Course::findOrFail($id);
        $course->update(['is_published' => ! $course->is_published]);
    }

    public function deleteCourse(int $id): void
    {
        if (! Auth::user()?->isBrandAdmin()) {
            return;
        }
        $course = Course::findOrFail($id);
        if ($course->thumbnail && str_starts_with($course->thumbnail, 'course/thumbnails/')) {
            Storage::disk('public')->delete($course->thumbnail);
        }
        $course->delete();
    }

    private function resetCourseForm(): void
    {
        $this->editingCourseId = null;
        $this->courseTitle = '';
        $this->courseDescription = '';
        $this->coursePillar = 'delivery';
        $this->courseDifficulty = 'basic';
        $this->courseMinLevel = 1;
        $this->courseXpReward = 0;
        $this->courseAipReward = 0;
        $this->coursePrice = '';
        $this->coursePublished = false;
        $this->courseFeatured = false;
        $this->courseThumbnail = null;
        $this->existingCourseThumbnail = null;
        $this->removeCourseThumbnail = false;
    }

    public function render(): View
    {
        $courses = Course::withCount(['modules', 'enrollments'])->latest()->get();

        return view('livewire.admin-courses', ['courses' => $courses])
            ->layout('layouts.app', ['title' => 'Quản lý khóa học — Admin']);
    }
}
