<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\LessonTask;
use App\Models\Module;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Learning\Application\CourseLearningService;
use Modules\Learning\Application\CourseLessonCompletionOutcome;
use Tests\TestCase;

final class CourseLearningServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_task_submission_completes_the_lesson_and_course_once(): void
    {
        $user = User::factory()->create(['xp' => 0]);
        [$course, $lesson, $task] = $this->courseLessonTask();
        $this->enroll($course, $user);

        $result = app(CourseLearningService::class)->submitTask($course, $task->id, $user, 'My completed work');

        self::assertTrue($result->accepted);
        self::assertSame(CourseLessonCompletionOutcome::CourseCompleted, $result->completion);
        $this->assertDatabaseHas('task_submissions', [
            'brand_id' => brand()->id,
            'lesson_task_id' => $task->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('lesson_progress', [
            'brand_id' => brand()->id,
            'lesson_id' => $lesson->id,
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('course_enrollments', [
            'brand_id' => brand()->id,
            'course_id' => $course->id,
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseCount('xp_transactions', 2);

        $retry = app(CourseLearningService::class)->submitTask($course, $task->id, $user, 'Updated work');
        self::assertTrue($retry->accepted);
        self::assertSame(CourseLessonCompletionOutcome::AlreadyCompleted, $retry->completion);
        $this->assertDatabaseCount('task_submissions', 1);
        $this->assertDatabaseCount('xp_transactions', 2);
    }

    public function test_progress_requires_an_active_enrollment_and_rejects_unrelated_task(): void
    {
        $user = User::factory()->create();
        [$course, $lesson] = $this->courseLessonTask();
        [, , $unrelatedTask] = $this->courseLessonTask('Unrelated course');
        $service = app(CourseLearningService::class);

        self::assertSame(CourseLessonCompletionOutcome::NotEnrolled, $service->complete($course, $lesson->id, $user));
        $this->enroll($course, $user);

        $result = $service->submitTask($course, $unrelatedTask->id, $user, 'Should not be stored');

        self::assertFalse($result->accepted);
        self::assertSame(CourseLessonCompletionOutcome::Unavailable, $result->completion);
        $this->assertDatabaseCount('task_submissions', 0);
    }

    public function test_progress_cannot_target_a_course_from_another_community(): void
    {
        $user = User::factory()->create();
        $otherBrand = $this->otherBrand();
        $course = Course::withoutGlobalScopes()->create([
            'brand_id' => $otherBrand->id,
            'title' => 'Other community course',
            'pillar' => 'delivery',
            'difficulty' => 'basic',
            'is_published' => true,
        ]);

        $this->expectException(AuthorizationException::class);
        app(CourseLearningService::class)->complete($course, 1, $user);
    }

    /** @return array{Course, Lesson, LessonTask} */
    private function courseLessonTask(string $title = 'Learning service course'): array
    {
        $course = Course::create([
            'brand_id' => brand()->id,
            'title' => $title,
            'pillar' => 'delivery',
            'difficulty' => 'basic',
            'xp_reward' => 50,
            'is_published' => true,
        ]);
        $module = Module::create(['course_id' => $course->id, 'title' => 'Module', 'order_index' => 0]);
        $lesson = Lesson::create([
            'module_id' => $module->id,
            'title' => 'Lesson',
            'xp_reward' => 10,
            'order_index' => 0,
            'is_locked_by_default' => false,
        ]);
        $task = LessonTask::create(['lesson_id' => $lesson->id, 'title' => 'Task', 'is_required' => true]);

        return [$course, $lesson, $task];
    }

    private function enroll(Course $course, User $user): void
    {
        CourseEnrollment::create([
            'brand_id' => brand()->id,
            'course_id' => $course->id,
            'user_id' => $user->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);
    }

    private function otherBrand(): Brand
    {
        return Brand::create([
            'name' => 'Other Learning Community',
            'slug' => 'other-learning-community',
            'domain' => 'other-learning-community.test',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#DCECF7',
            'theme_bg' => '#F8FAFC',
            'registration_mode' => 'invite',
            'is_invite_only' => true,
        ]);
    }
}
