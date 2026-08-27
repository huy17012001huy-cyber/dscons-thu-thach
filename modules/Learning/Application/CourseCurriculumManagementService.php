<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

use App\Core\CommunityContext;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonTask;
use App\Models\Module;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class CourseCurriculumManagementService
{
    public function __construct(private readonly CommunityContext $context) {}

    public function addModule(Course $course, User $actor, string $title): ?Module
    {
        return DB::transaction(function () use ($course, $actor, $title): ?Module {
            $course = $this->managedCourse($course, $actor);
            if (! $course) {
                return null;
            }

            return Module::create([
                'course_id' => $course->id,
                'title' => $title,
                'order_index' => ($course->modules()->lockForUpdate()->max('order_index') ?? -1) + 1,
            ]);
        });
    }

    public function deleteModule(Course $course, User $actor, int $moduleId): bool
    {
        return DB::transaction(function () use ($course, $actor, $moduleId): bool {
            $course = $this->managedCourse($course, $actor);

            return $course && Module::query()
                ->where('course_id', $course->id)
                ->whereKey($moduleId)
                ->lockForUpdate()
                ->delete() > 0;
        });
    }

    /** @param array{title:string,lesson_type:string,xp_reward:int,is_locked_by_default:bool} $attributes */
    public function addLesson(Course $course, User $actor, int $moduleId, array $attributes): ?Lesson
    {
        return DB::transaction(function () use ($course, $actor, $moduleId, $attributes): ?Lesson {
            $course = $this->managedCourse($course, $actor);
            $module = $course ? Module::query()->where('course_id', $course->id)->whereKey($moduleId)->lockForUpdate()->first() : null;
            if (! $module) {
                return null;
            }

            return Lesson::create([
                ...$attributes,
                'module_id' => $module->id,
                'order_index' => ($module->lessons()->lockForUpdate()->max('order_index') ?? -1) + 1,
            ]);
        });
    }

    public function deleteLesson(Course $course, User $actor, int $lessonId): bool
    {
        return DB::transaction(function () use ($course, $actor, $lessonId): bool {
            $course = $this->managedCourse($course, $actor);

            return $course && Lesson::query()->whereKey($lessonId)
                ->whereHas('module', fn ($query) => $query->where('course_id', $course->id))
                ->lockForUpdate()
                ->delete() > 0;
        });
    }

    /** @param array{title:string,description:?string,type:string,is_required:bool} $attributes */
    public function addTask(Course $course, User $actor, int $lessonId, array $attributes): ?LessonTask
    {
        return DB::transaction(function () use ($course, $actor, $lessonId, $attributes): ?LessonTask {
            $course = $this->managedCourse($course, $actor);
            $lesson = $course ? Lesson::query()->whereKey($lessonId)
                ->whereHas('module', fn ($query) => $query->where('course_id', $course->id))
                ->lockForUpdate()->first() : null;
            if (! $lesson) {
                return null;
            }

            return LessonTask::create([
                ...$attributes,
                'lesson_id' => $lesson->id,
                'order_index' => ($lesson->tasks()->lockForUpdate()->max('order_index') ?? -1) + 1,
            ]);
        });
    }

    public function deleteTask(Course $course, User $actor, int $taskId): bool
    {
        return DB::transaction(function () use ($course, $actor, $taskId): bool {
            $course = $this->managedCourse($course, $actor);

            return $course && LessonTask::query()->whereKey($taskId)
                ->whereHas('lesson.module', fn ($query) => $query->where('course_id', $course->id))
                ->lockForUpdate()
                ->delete() > 0;
        });
    }

    private function managedCourse(Course $course, User $actor): ?Course
    {
        $brand = $this->context->require();
        if ($course->brand_id !== $brand->id) {
            throw new AuthorizationException('Course does not belong to the current community.');
        }
        if (! $actor->isCommunityAdmin($brand->id)) {
            return null;
        }

        return Course::query()->where('brand_id', $brand->id)->whereKey($course->id)->lockForUpdate()->firstOrFail();
    }
}
