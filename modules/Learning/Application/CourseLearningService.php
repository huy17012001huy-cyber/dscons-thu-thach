<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

use App\Core\CommunityContext;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\LessonTask;
use App\Models\TaskSubmission;
use App\Models\User;
use App\Services\XpService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class CourseLearningService
{
    public function __construct(
        private readonly CommunityContext $context,
        private readonly XpService $xp,
    ) {}

    public function submitTask(Course $course, int $taskId, User $user, string $content): CourseTaskSubmissionResult
    {
        return DB::transaction(function () use ($course, $taskId, $user, $content): CourseTaskSubmissionResult {
            $course = $this->lockedCourse($course);
            $enrollment = $this->activeEnrollment($course, $user);
            if (! $enrollment) {
                return new CourseTaskSubmissionResult(false, CourseLessonCompletionOutcome::NotEnrolled);
            }

            $task = $this->taskForCourse($course, $taskId);
            if (! $task) {
                return new CourseTaskSubmissionResult(false, CourseLessonCompletionOutcome::Unavailable);
            }

            $this->saveSubmission($task, $user, $content, $course->brand_id);
            $lesson = $this->lockedLesson($course, $task->lesson_id);
            if (! $lesson) {
                return new CourseTaskSubmissionResult(false, CourseLessonCompletionOutcome::Unavailable);
            }
            $completion = $this->requiredTasksSubmitted($lesson, $user, $course->brand_id)
                ? $this->completeLesson($course, $lesson, $enrollment, $user)
                : CourseLessonCompletionOutcome::Unavailable;

            return new CourseTaskSubmissionResult(true, $completion);
        });
    }

    public function complete(Course $course, int $lessonId, User $user): CourseLessonCompletionOutcome
    {
        return DB::transaction(function () use ($course, $lessonId, $user): CourseLessonCompletionOutcome {
            $course = $this->lockedCourse($course);
            $enrollment = $this->activeEnrollment($course, $user);
            if (! $enrollment) {
                return CourseLessonCompletionOutcome::NotEnrolled;
            }

            $lesson = $this->lockedLesson($course, $lessonId);
            if (! $lesson) {
                return CourseLessonCompletionOutcome::Unavailable;
            }

            return $this->completeLesson($course, $lesson, $enrollment, $user);
        });
    }

    private function lockedCourse(Course $course): Course
    {
        $brand = $this->context->require();
        if ($course->brand_id !== $brand->id) {
            throw new AuthorizationException('Course does not belong to the current community.');
        }

        return Course::query()
            ->where('brand_id', $brand->id)
            ->whereKey($course->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function activeEnrollment(Course $course, User $user): ?CourseEnrollment
    {
        return CourseEnrollment::query()
            ->where('brand_id', $course->brand_id)
            ->where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->lockForUpdate()
            ->first();
    }

    private function taskForCourse(Course $course, int $taskId): ?LessonTask
    {
        return LessonTask::query()
            ->whereKey($taskId)
            ->whereHas('lesson.module', function (Builder $query) use ($course): void {
                $query->where('course_id', $course->id);
            })
            ->lockForUpdate()
            ->first();
    }

    private function lockedLesson(Course $course, int $lessonId): ?Lesson
    {
        return Lesson::query()
            ->whereKey($lessonId)
            ->whereHas('module', function (Builder $query) use ($course): void {
                $query->where('course_id', $course->id);
            })
            ->lockForUpdate()
            ->first();
    }

    private function saveSubmission(LessonTask $task, User $user, string $content, int $brandId): void
    {
        $submission = TaskSubmission::query()
            ->where('brand_id', $brandId)
            ->where('lesson_task_id', $task->id)
            ->where('user_id', $user->id)
            ->lockForUpdate()
            ->first();
        $attributes = [
            'content' => $content,
            'status' => 'pending',
            'submitted_at' => now(),
        ];

        if ($submission) {
            $submission->update($attributes);

            return;
        }

        TaskSubmission::create([
            'brand_id' => $brandId,
            'lesson_task_id' => $task->id,
            'user_id' => $user->id,
            ...$attributes,
        ]);
    }

    private function requiredTasksSubmitted(Lesson $lesson, User $user, int $brandId): bool
    {
        $requiredTaskIds = $lesson->tasks()->where('is_required', true)->pluck('id');
        if ($requiredTaskIds->isEmpty()) {
            return false;
        }

        return TaskSubmission::query()
            ->where('brand_id', $brandId)
            ->where('user_id', $user->id)
            ->whereIn('lesson_task_id', $requiredTaskIds)
            ->whereIn('status', ['pending', 'approved'])
            ->count() >= $requiredTaskIds->count();
    }

    private function completeLesson(
        Course $course,
        Lesson $lesson,
        CourseEnrollment $enrollment,
        User $user,
    ): CourseLessonCompletionOutcome {
        if (! $lesson->isUnlockedFor($user)) {
            return CourseLessonCompletionOutcome::Locked;
        }

        $progress = LessonProgress::query()
            ->where('brand_id', $course->brand_id)
            ->where('lesson_id', $lesson->id)
            ->where('user_id', $user->id)
            ->lockForUpdate()
            ->first();
        if ($progress?->completed_at) {
            return CourseLessonCompletionOutcome::AlreadyCompleted;
        }

        if ($progress) {
            $progress->update(['completed_at' => now()]);
        } else {
            LessonProgress::create([
                'brand_id' => $course->brand_id,
                'lesson_id' => $lesson->id,
                'user_id' => $user->id,
                'completed_at' => now(),
            ]);
        }

        if ($lesson->xp_reward > 0) {
            $this->xp->award($user, 'lesson_complete', 1.0, 'Hoàn thành bài: '.$lesson->title, $lesson);
        }

        if (! $this->courseCompleted($course, $user) || $enrollment->completed_at) {
            return CourseLessonCompletionOutcome::Completed;
        }

        $enrollment->update(['completed_at' => now()]);
        if ($course->xp_reward > 0) {
            $this->xp->award($user, 'course_complete', 1.0, 'Hoàn thành khóa học: '.$course->title, $course);
        }

        return CourseLessonCompletionOutcome::CourseCompleted;
    }

    private function courseCompleted(Course $course, User $user): bool
    {
        $lessonIds = Lesson::query()
            ->whereHas('module', function (Builder $query) use ($course): void {
                $query->where('course_id', $course->id);
            })
            ->pluck('id');

        return $lessonIds->isNotEmpty()
            && LessonProgress::query()
                ->where('brand_id', $course->brand_id)
                ->where('user_id', $user->id)
                ->whereIn('lesson_id', $lessonIds)
                ->whereNotNull('completed_at')
                ->count() >= $lessonIds->count();
    }
}
